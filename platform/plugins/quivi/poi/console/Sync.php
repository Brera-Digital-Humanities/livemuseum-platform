<?php namespace Quivi\Poi\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Carbon\Carbon;
use Db;
use Illuminate\Support\Str;

class Sync extends Command
{
    protected $dbPath;

    protected $stats = [
        'files' => 0,
        'read' => 0,
        'created' => 0,
        'updated' => 0,
        'sources' => 0,
        'skipped' => 0,
    ];

    protected $preferredFiles = [
        'Chiese Italiane/vecchi/chiese_con_gps.json',
        'Chiese Italiane/CHIESE [25-05]/chiese_arricchite.json',
        'Beni Immobili/beni_immobili_v2.json',
        'Dati Wikidata/nuovi_wikidata_v3.json',
        'Luoghi della Cultura [10-05]/luoghi_cultura_v2.json',
    ];

    protected $poiByUri = [];

    protected $poiByDedupeKey = [];

    protected $sourceByKey = [];

    /**
     * @var string The console command name.
     *
     * php artisan Quivi.Poi:Sync db --filename="Chiese Italiane/CHIESE [25-05]/chiese_arricchite.json"
     * 
     * 
     * /usr/bin/php artisan Quivi.Poi:Sync db --all=1
     */
    protected $name = 'Quivi.Poi:Sync';

    /**
     * @var string The console command description.
     */
    protected $description = 'Sync Poi DB';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        if (method_exists($this, 'sync_'.$this->argument('type'))){
            $this->{'sync_'.$this->argument('type')}();
        } else {
            $this->output->writeln('SYNC TYPE not found');
        }

        $this->output->writeln('**********************************');
    }

    protected function sync_db(){
        $this->dbPath = storage_path('app/db');
        $files = $this->filesToImport();

        if (!$files) {
            $this->output->writeln('Missing filename or --all=1');
            die();
        }

        $this->primeCaches();

        foreach ($files as $file) {
            $this->importFile($file);
        }

        $this->output->writeln(sprintf(
            'POI import completed. files=%d read=%d created=%d updated=%d sources=%d skipped=%d',
            $this->stats['files'],
            $this->stats['read'],
            $this->stats['created'],
            $this->stats['updated'],
            $this->stats['sources'],
            $this->stats['skipped']
        ));
    }


    protected function sync_none(){}


    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['type', InputArgument::OPTIONAL, 'Sync type', 'none'],
        ];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['all', null, InputOption::VALUE_OPTIONAL, 'sync all (optional)', null],
            ['filename', null, InputOption::VALUE_OPTIONAL, 'get file name'],
            ['limit', null, InputOption::VALUE_OPTIONAL, 'limit imported records per file', null],
        ];
    }

    protected function filesToImport()
    {
        if ($this->option('filename')) {
            $file = $this->resolveJsonPath($this->option('filename'));

            return $file ? [$file] : [];
        }

        if (!$this->option('all')) {
            return [];
        }

        $files = [];
        foreach ($this->preferredFiles as $relativePath) {
            $file = $this->resolveJsonPath($relativePath);
            if ($file) {
                $files[] = $file;
            } else {
                $this->output->writeln('Preferred file not found: ' . $relativePath);
            }
        }

        return $files;
    }

    protected function resolveJsonPath($filename)
    {
        $filename = trim($filename);
        $path = $filename;

        if (!$this->isAbsolutePath($path)) {
            $path = $this->dbPath . '/' . ltrim($filename, '/');
        }

        $realDbPath = realpath($this->dbPath);
        $realPath = realpath($path);

        if (!$realPath || !$realDbPath || strpos($realPath, $realDbPath) !== 0 || strtolower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'json') {
            return null;
        }

        return $realPath;
    }

    protected function isAbsolutePath($path)
    {
        return isset($path[0]) && $path[0] === '/';
    }

    protected function importFile($file)
    {
        $relativeFile = $this->relativePath($file);
        $this->stats['files']++;
        $this->output->writeln('Importing ' . $relativeFile);

        $count = 0;
        $limit = (int) $this->option('limit');
        foreach ($this->jsonRecords($file) as $record) {
            $count++;
            $this->stats['read']++;

            if (!is_array($record)) {
                $this->stats['skipped']++;
                continue;
            }

            $this->importRecord($record, $relativeFile);

            if ($count % 1000 === 0) {
                $this->output->writeln('  processed ' . $count);
            }

            if ($limit > 0 && $count >= $limit) {
                break;
            }
        }

        $this->output->writeln('Imported records from file: ' . $count);
    }

    protected function jsonRecords($file)
    {
        $handle = fopen($file, 'r');
        if (!$handle) {
            return;
        }

        $buffer = '';
        $depth = 0;
        $inString = false;
        $escape = false;
        $started = false;

        while (!feof($handle)) {
            $chunk = fread($handle, 8192);
            $length = strlen($chunk);

            for ($i = 0; $i < $length; $i++) {
                $char = $chunk[$i];

                if (!$started) {
                    if ($char === '{') {
                        $started = true;
                        $depth = 1;
                        $buffer = '{';
                    }
                    continue;
                }

                $buffer .= $char;

                if ($escape) {
                    $escape = false;
                    continue;
                }

                if ($char === '\\' && $inString) {
                    $escape = true;
                    continue;
                }

                if ($char === '"') {
                    $inString = !$inString;
                    continue;
                }

                if ($inString) {
                    continue;
                }

                if ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $record = json_decode($buffer, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            yield $record;
                        } else {
                            $this->stats['skipped']++;
                            $this->output->writeln('Invalid JSON record skipped in ' . $this->relativePath($file) . ': ' . json_last_error_msg());
                        }

                        $buffer = '';
                        $started = false;
                        $inString = false;
                        $escape = false;
                    }
                }
            }
        }

        fclose($handle);
    }

    protected function importRecord(array $record, $relativeFile)
    {
        $data = $this->mapRecord($record, $relativeFile);

        if (!$data['id_opendata'] && !$data['dedupe_key']) {
            $this->stats['skipped']++;
            return;
        }

        $poi = $this->findExistingPoi($data);
        $now = Carbon::now();

        if ($poi) {
            $merged = $this->mergePoiData((array) $poi, $data);
            $merged['updated_at'] = $now;
            $merged['imported_at'] = $now;
            Db::table('quivi_poi_pois')->where('id', $poi->id)->update($merged);
            $poiId = $poi->id;
            $this->rememberPoi($poiId, $data);
            $this->stats['updated']++;
        } else {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $data['imported_at'] = $now;
            $poiId = Db::table('quivi_poi_pois')->insertGetId($data);
            $this->rememberPoi($poiId, $data);
            $this->stats['created']++;
        }

        $this->upsertSource($poiId, $data['source'], $relativeFile, $data['id_opendata'], $record);
    }

    protected function mapRecord(array $record, $relativeFile)
    {
        $uri = $this->cleanString($record['uri'] ?? $record['identificativo'] ?? null);
        $title = $this->cleanString($record['nome'] ?? $record['denominazione_beweb'] ?? null);
        $type = $this->cleanString($record['tipologia'] ?? $record['tipo'] ?? $record['qualificazione'] ?? null);
        $category = $this->cleanString($record['categoria_app'] ?? null);
        $address = $this->cleanString($record['indirizzo'] ?? null);
        $city = $this->cleanString($record['comune'] ?? null);
        $province = $this->cleanString($record['provincia'] ?? null);
        $region = $this->cleanString($record['regione'] ?? null);
        $lat = $this->coordinate($record['latitudine'] ?? null, -90, 90);
        $lng = $this->coordinate($record['longitudine'] ?? null, -180, 180);
        $description = $this->description($record);
        $source = $this->sourceName($record, $relativeFile);
        $sourceFile = $relativeFile;

        $data = [
            'id_opendata' => $uri,
            'code' => $this->code($record, $uri, $title),
            'title' => $title,
            'slug' => $title ? Str::slug($title) : null,
            'type' => $type,
            'category_app' => $category,
            'scheda_type' => 'base',
            'fulladdress' => $this->fullAddress($address, $city, $province, $region),
            'address' => $address,
            'city' => $city,
            'province' => $province,
            'region' => $region,
            'zipcode' => $this->cleanString($record['cap'] ?? $record['zipcode'] ?? null),
            'lat' => $lat,
            'lng' => $lng,
            'descr' => $description,
            'image_url' => $this->imageUrl($record),
            'phone' => $this->cleanString($record['telefono'] ?? null),
            'email' => $this->cleanString($record['email'] ?? null),
            'website' => $this->cleanString($record['sito_web'] ?? $record['link_wikipedia'] ?? null),
            'opening_hours' => $this->jsonValue($record['orari'] ?? null),
            'tickets_info' => $this->cleanString($record['biglietto'] ?? null),
            'booking_info' => $this->cleanString($record['prenotazione'] ?? null),
            'source' => $source,
            'source_file' => $sourceFile,
            'dedupe_key' => $this->dedupeKey($title, $lat, $lng, $city),
            'raw_data' => $this->jsonValue($record),
        ];

        if ($lat !== null && $lng !== null) {
            $data['location'] = Db::raw('ST_SRID(POINT(' . $lng . ', ' . $lat . '), 4326)');
        }

        return $data;
    }

    protected function findExistingPoi(array $data)
    {
        if ($data['id_opendata']) {
            if (isset($this->poiByUri[$data['id_opendata']])) {
                return Db::table('quivi_poi_pois')->where('id', $this->poiByUri[$data['id_opendata']])->first();
            }

            $sourceKey = $this->sourceKey($data['source'], $data['id_opendata']);
            if (isset($this->sourceByKey[$sourceKey])) {
                return Db::table('quivi_poi_pois')->where('id', $this->sourceByKey[$sourceKey]['poi_id'])->first();
            }
        }

        if ($data['dedupe_key'] && isset($this->poiByDedupeKey[$data['dedupe_key']])) {
            return Db::table('quivi_poi_pois')->where('id', $this->poiByDedupeKey[$data['dedupe_key']])->first();
        }

        return null;
    }

    protected function mergePoiData(array $existing, array $incoming)
    {
        $merged = [];
        $columns = [
            'id_opendata',
            'code',
            'title',
            'slug',
            'type',
            'category_app',
            'scheda_type',
            'fulladdress',
            'address',
            'city',
            'province',
            'region',
            'zipcode',
            'lat',
            'lng',
            'descr',
            'image_url',
            'phone',
            'email',
            'website',
            'opening_hours',
            'tickets_info',
            'booking_info',
            'source',
            'source_file',
            'dedupe_key',
            'raw_data',
        ];

        foreach ($columns as $column) {
            if (!array_key_exists($column, $incoming)) {
                continue;
            }

            $current = $existing[$column] ?? null;
            $next = $incoming[$column];

            if ($this->shouldReplace($column, $current, $next)) {
                $merged[$column] = $next;
            }
        }

        if (array_key_exists('location', $incoming) && ($this->emptyValue($existing['location'] ?? null) || $this->shouldReplaceCoordinate($existing, $incoming))) {
            $merged['location'] = $incoming['location'];
        }

        return $merged;
    }

    protected function shouldReplace($column, $current, $next)
    {
        if ($next === null || $next === '') {
            return false;
        }

        if ($this->emptyValue($current)) {
            return true;
        }

        if ($column === 'descr') {
            return strlen((string) $next) > strlen((string) $current);
        }

        if ($column === 'category_app') {
            return $current === 'Altro' && $next !== 'Altro';
        }

        if (in_array($column, ['lat', 'lng'], true)) {
            return false;
        }

        if (in_array($column, ['raw_data', 'source_file', 'source'], true)) {
            return true;
        }

        return false;
    }

    protected function shouldReplaceCoordinate(array $existing, array $incoming)
    {
        return $this->emptyValue($existing['lat'] ?? null)
            && $this->emptyValue($existing['lng'] ?? null)
            && !$this->emptyValue($incoming['lat'] ?? null)
            && !$this->emptyValue($incoming['lng'] ?? null);
    }

    protected function upsertSource($poiId, $source, $sourceFile, $uri, array $record)
    {
        if (!$uri) {
            return;
        }

        $now = Carbon::now();
        $payload = $this->jsonValue($record);
        $key = $this->sourceKey($source, $uri);
        $existing = $this->sourceByKey[$key] ?? null;

        if ($existing) {
            Db::table('quivi_poi_sources')->where('id', $existing['id'])->update([
                'poi_id' => $poiId,
                'source_file' => $sourceFile,
                'payload' => $payload,
                'updated_at' => $now,
            ]);
        } else {
            Db::table('quivi_poi_sources')->insert([
                'poi_id' => $poiId,
                'source' => $source,
                'source_file' => $sourceFile,
                'uri' => $uri,
                'payload' => $payload,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->stats['sources']++;
        }

        $this->sourceByKey[$key] = [
            'id' => $existing ? $existing['id'] : (int) Db::getPdo()->lastInsertId(),
            'poi_id' => $poiId,
        ];
    }

    protected function primeCaches()
    {
        $this->output->writeln('Loading existing POI keys');

        Db::table('quivi_poi_pois')
            ->select('id', 'id_opendata', 'dedupe_key')
            ->orderBy('id')
            ->chunk(5000, function ($rows) {
                foreach ($rows as $row) {
                    if ($row->id_opendata) {
                        $this->poiByUri[$row->id_opendata] = $row->id;
                    }
                    if ($row->dedupe_key) {
                        $this->poiByDedupeKey[$row->dedupe_key] = $row->id;
                    }
                }
            });

        Db::table('quivi_poi_sources')
            ->select('id', 'poi_id', 'source', 'uri')
            ->orderBy('id')
            ->chunk(5000, function ($rows) {
                foreach ($rows as $row) {
                    if ($row->uri) {
                        $this->sourceByKey[$this->sourceKey($row->source, $row->uri)] = [
                            'id' => $row->id,
                            'poi_id' => $row->poi_id,
                        ];
                    }
                }
            });

        $this->output->writeln(sprintf(
            'Loaded keys. uri=%d dedupe=%d sources=%d',
            count($this->poiByUri),
            count($this->poiByDedupeKey),
            count($this->sourceByKey)
        ));
    }

    protected function rememberPoi($poiId, array $data)
    {
        if ($data['id_opendata']) {
            $this->poiByUri[$data['id_opendata']] = $poiId;
        }
        if ($data['dedupe_key']) {
            $this->poiByDedupeKey[$data['dedupe_key']] = $poiId;
        }
    }

    protected function sourceKey($source, $uri)
    {
        return md5((string) $source . '|' . (string) $uri);
    }

    protected function description(array $record)
    {
        $fields = [
            'descrizione',
            'wikipedia_extract',
            'descrizione_beweb',
            'descrizione_wikidata',
            'notizie_storiche',
        ];

        $best = null;
        foreach ($fields as $field) {
            $value = $this->cleanString($record[$field] ?? null);
            if ($value && (!$best || strlen($value) > strlen($best))) {
                $best = $value;
            }
        }

        return $best;
    }

    protected function imageUrl(array $record)
    {
        $image = $this->cleanString($record['immagine'] ?? $record['immagine_commons'] ?? null);
        if ($image) {
            return $image;
        }

        if (!empty($record['immagini']) && is_array($record['immagini'])) {
            foreach ($record['immagini'] as $item) {
                if (is_string($item)) {
                    return $this->cleanString($item);
                }
                if (is_array($item)) {
                    $candidate = $this->cleanString($item['url'] ?? $item['src'] ?? null);
                    if ($candidate) {
                        return $candidate;
                    }
                }
            }
        }

        return null;
    }

    protected function sourceName(array $record, $relativeFile)
    {
        $source = $this->cleanString($record['fonte'] ?? null);
        if ($source) {
            return $source;
        }

        $parts = explode('/', $relativeFile);
        return $parts[0] ?? 'Import JSON';
    }

    protected function code(array $record, $uri, $title)
    {
        $code = $this->cleanString($record['beweb_id'] ?? $record['wikidata_id'] ?? $record['identificativo'] ?? null);
        if ($code) {
            return $code;
        }

        if ($uri) {
            $path = trim(parse_url($uri, PHP_URL_PATH) ?: '', '/');
            if ($path) {
                return substr(Str::slug(str_replace('/', '-', $path)), 0, 255);
            }
        }

        return $title ? substr(Str::slug($title), 0, 255) : null;
    }

    protected function dedupeKey($title, $lat, $lng, $city)
    {
        if (!$title) {
            return null;
        }

        $parts = [Str::slug($title)];
        if ($lat !== null && $lng !== null) {
            $parts[] = number_format((float) $lat, 5, '.', '');
            $parts[] = number_format((float) $lng, 5, '.', '');
        } elseif ($city) {
            $parts[] = Str::slug($city);
        } else {
            return null;
        }

        return substr(implode('|', $parts), 0, 255);
    }

    protected function fullAddress($address, $city, $province, $region)
    {
        $parts = array_filter([$address, $city, $province, $region], function ($value) {
            return !$this->emptyValue($value);
        });

        return $parts ? implode(' - ', $parts) : null;
    }

    protected function coordinate($value, $min, $max)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (float) str_replace(',', '.', (string) $value);
        if ($value < $min || $value > $max || $value == 0.0) {
            return null;
        }

        return round($value, 7);
    }

    protected function cleanString($value)
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return null;
        }

        $value = trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $value = preg_replace('/\s+/u', ' ', $value);

        return $value === '' ? null : $value;
    }

    protected function jsonValue($value)
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function emptyValue($value)
    {
        return $value === null || $value === '';
    }

    protected function relativePath($file)
    {
        $prefix = rtrim($this->dbPath, '/') . '/';

        return strpos($file, $prefix) === 0 ? substr($file, strlen($prefix)) : $file;
    }

}

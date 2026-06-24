<?php namespace Quivi\Poi\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Carbon\Carbon;
use Db;
use Illuminate\Support\Str;
use Quivi\Poi\Models\Poi;
use System\Models\File as SystemFile;
use Throwable;

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

    protected function sync_imageurls(){
        $limit = (int) $this->option('limit');
        $stats = [
            'processed' => 0,
            'downloaded' => 0,
            'skipped' => 0,
            'failed' => 0,
            'already_attached' => 0,
        ];

        $attachmentType = (new Poi)->getMorphClass();

        $query = Db::table('quivi_poi_pois as poi')
            ->leftJoin('system_files as image', function ($join) use ($attachmentType) {
                $join->on('image.attachment_id', '=', 'poi.id')
                    ->where('image.attachment_type', '=', $attachmentType)
                    ->where('image.field', '=', 'image');
            })
            ->select('poi.id', 'poi.title', 'poi.image_url')
            ->whereNull('poi.deleted_at')
            ->whereNotNull('poi.image_url')
            ->where('poi.image_url', '<>', '')
            ->whereNull('image.id')
            ->orderBy('poi.id');

        $this->output->writeln('Syncing POI images from image_url');

        $lastId = 0;
        while (true) {
            $rows = (clone $query)
                ->where('poi.id', '>', $lastId)
                ->limit(100)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            foreach ($rows as $row) {
                if ($limit > 0 && $stats['processed'] >= $limit) {
                    break 2;
                }

                $lastId = (int) $row->id;
                $stats['processed']++;

                $poi = Poi::find((int) $row->id);
                if (!$poi) {
                    $stats['skipped']++;
                    continue;
                }

                if ($poi->image) {
                    $stats['already_attached']++;
                    continue;
                }

                $urls = $this->poiImageSyncCandidateUrls($row->image_url, $poi->raw_data ?? null);
                if (!$urls) {
                    $stats['skipped']++;
                    $this->writeImageSyncSkip($row->id, 'invalid URL');
                    continue;
                }

                try {
                    $result = $this->downloadAndAttachFirstPoiImage($poi, $urls);
                } catch (Throwable $e) {
                    $stats['failed']++;
                    $this->writeImageSyncSkip($row->id, $e->getMessage());
                    continue;
                }

                if ($result['ok']) {
                    $stats['downloaded']++;
                    if ($this->isImageSyncVerbose()) {
                        $this->output->writeln(sprintf('  downloaded #%d %s', $row->id, $result['path']));
                    }
                } else {
                    $stats['skipped']++;
                    $this->writeImageSyncSkip($row->id, $result['reason']);
                }

                if ($stats['processed'] % 100 === 0) {
                    $this->output->writeln(sprintf(
                        '  processed=%d downloaded=%d skipped=%d failed=%d',
                        $stats['processed'],
                        $stats['downloaded'],
                        $stats['skipped'],
                        $stats['failed']
                    ));
                }
            }
        }

        $this->output->writeln(sprintf(
            'POI image sync completed. processed=%d downloaded=%d skipped=%d failed=%d already_attached=%d',
            $stats['processed'],
            $stats['downloaded'],
            $stats['skipped'],
            $stats['failed'],
            $stats['already_attached']
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

    protected function cleanImageSourceUrl($url)
    {
        $url = $this->cleanString($url);
        if (!$url) {
            return null;
        }

        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        $url = $this->commonsImageDownloadUrl($url);

        return $url;
    }

    protected function poiImageSyncCandidateUrls($primaryUrl, $rawData = null)
    {
        $urls = [];
        $this->appendImageSyncCandidateUrl($urls, $primaryUrl);

        foreach ($this->rawDataImageUrls($rawData) as $url) {
            $this->appendImageSyncCandidateUrl($urls, $url);
        }

        return $urls;
    }

    protected function appendImageSyncCandidateUrl(array &$urls, $url)
    {
        $url = $this->cleanImageSourceUrl($url);
        if (!$this->isDownloadableImageUrl($url) || in_array($url, $urls, true)) {
            return;
        }

        $urls[] = $url;
    }

    protected function rawDataImageUrls($rawData)
    {
        if (!$rawData) {
            return [];
        }

        if (is_string($rawData)) {
            $rawData = json_decode($rawData, true);
        }

        if (!is_array($rawData)) {
            return [];
        }

        $urls = [
            $rawData['immagine'] ?? null,
            $rawData['immagine_commons'] ?? null,
        ];

        foreach (['immagini', 'immagini_iiif'] as $field) {
            if (empty($rawData[$field]) || !is_array($rawData[$field])) {
                continue;
            }

            foreach ($rawData[$field] as $item) {
                if (is_string($item)) {
                    $urls[] = $item;
                    continue;
                }

                if (is_array($item)) {
                    $urls[] = $item['url'] ?? $item['src'] ?? $item['image'] ?? $item['immagine'] ?? null;
                }
            }
        }

        return $urls;
    }

    protected function commonsImageDownloadUrl($url)
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';

        if ($host !== 'commons.wikimedia.org' || strpos($path, '/wiki/Special:FilePath/') === false) {
            return $url;
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        if (empty($query['width'])) {
            $query['width'] = (string) $this->commonsImageWidth();
        }

        $rebuilt = ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . $path;
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        if ($queryString !== '') {
            $rebuilt .= '?' . $queryString;
        }
        if (!empty($parts['fragment'])) {
            $rebuilt .= '#' . $parts['fragment'];
        }

        return $rebuilt;
    }

    protected function isDownloadableImageUrl($url)
    {
        if (!$url) {
            return false;
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');

        return in_array($scheme, ['http', 'https'], true) && !empty($parts['host']);
    }

    protected function downloadAndAttachPoiImage(Poi $poi, $url)
    {
        $download = $this->downloadImageUrl($url);
        if (!$download['ok']) {
            return $download;
        }

        $tempPath = $download['path'];

        try {
            $image = $this->downloadedImageInfo($tempPath, $download['content_type']);
            if (!$image['ok']) {
                return $image;
            }

            $filename = $this->poiImageFilename(
                $poi,
                $download['effective_url'] ?: $url,
                $image['extension']
            );

            $file = new SystemFile;
            $file->is_public = true;
            $file->fromFile($tempPath, $filename);
            $poi->image()->add($file);

            return [
                'ok' => true,
                'path' => $file->getPath(),
            ];
        } finally {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    protected function downloadAndAttachFirstPoiImage(Poi $poi, array $urls)
    {
        $reasons = [];

        foreach ($urls as $url) {
            $result = $this->downloadAndAttachPoiImage($poi, $url);
            if ($result['ok']) {
                return $result;
            }

            $reasons[] = $this->shortImageSyncUrl($url) . ': ' . ($result['reason'] ?? 'download failed');
        }

        return [
            'ok' => false,
            'reason' => implode('; ', array_slice($reasons, 0, 3)) . (count($reasons) > 3 ? '; ...' : ''),
        ];
    }

    protected function downloadImageUrl($url)
    {
        if (function_exists('curl_init')) {
            return $this->downloadImageUrlWithCurl($url);
        }

        return $this->downloadImageUrlWithStreams($url);
    }

    protected function downloadImageUrlWithCurl($url)
    {
        $tempPath = temp_path('poi-image-' . uniqid('', true) . '.download');
        $handle = fopen($tempPath, 'w+b');
        if (!$handle) {
            return ['ok' => false, 'reason' => 'cannot create temp file'];
        }

        $maxBytes = $this->imageDownloadMaxBytes();
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_FILE => $handle,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_FAILONERROR => false,
            CURLOPT_USERAGENT => 'LiveMuseum POI image sync/1.0',
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => function ($resource, $downloadSize, $downloaded) use ($maxBytes) {
                return $downloaded > $maxBytes ? 1 : 0;
            },
        ]);

        if (defined('CURLOPT_PROTOCOLS')) {
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }
        if (defined('CURLOPT_REDIR_PROTOCOLS')) {
            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }

        $ok = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        curl_close($ch);
        fclose($handle);

        if (!$ok || $errno) {
            @unlink($tempPath);
            $reason = $errno === CURLE_ABORTED_BY_CALLBACK ? 'image exceeds max size' : trim($error);
            return ['ok' => false, 'reason' => $reason ?: 'download failed'];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            @unlink($tempPath);
            return ['ok' => false, 'reason' => 'HTTP ' . $httpCode];
        }

        clearstatcache(true, $tempPath);
        $size = is_file($tempPath) ? filesize($tempPath) : 0;
        if ($size <= 0) {
            @unlink($tempPath);
            return ['ok' => false, 'reason' => 'empty response'];
        }

        if ($size > $maxBytes) {
            @unlink($tempPath);
            return ['ok' => false, 'reason' => 'image exceeds max size'];
        }

        return [
            'ok' => true,
            'path' => $tempPath,
            'content_type' => $contentType,
            'effective_url' => $effectiveUrl,
        ];
    }

    protected function downloadImageUrlWithStreams($url)
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 45,
                'follow_location' => 1,
                'max_redirects' => 5,
                'ignore_errors' => true,
                'header' => "User-Agent: LiveMuseum POI image sync/1.0\r\n",
            ],
        ]);

        $remote = @fopen($url, 'rb', false, $context);
        if (!$remote) {
            return ['ok' => false, 'reason' => 'download failed'];
        }

        $tempPath = temp_path('poi-image-' . uniqid('', true) . '.download');
        $local = fopen($tempPath, 'w+b');
        if (!$local) {
            fclose($remote);
            return ['ok' => false, 'reason' => 'cannot create temp file'];
        }

        $maxBytes = $this->imageDownloadMaxBytes();
        $bytes = 0;
        while (!feof($remote)) {
            $chunk = fread($remote, 8192);
            if ($chunk === false) {
                fclose($remote);
                fclose($local);
                @unlink($tempPath);
                return ['ok' => false, 'reason' => 'download failed'];
            }

            $bytes += strlen($chunk);
            if ($bytes > $maxBytes) {
                fclose($remote);
                fclose($local);
                @unlink($tempPath);
                return ['ok' => false, 'reason' => 'image exceeds max size'];
            }

            fwrite($local, $chunk);
        }

        $meta = stream_get_meta_data($remote);
        fclose($remote);
        fclose($local);

        $headers = $meta['wrapper_data'] ?? [];
        $status = $this->httpStatusFromHeaders($headers);
        if ($status < 200 || $status >= 300) {
            @unlink($tempPath);
            return ['ok' => false, 'reason' => 'HTTP ' . $status];
        }

        if ($bytes <= 0) {
            @unlink($tempPath);
            return ['ok' => false, 'reason' => 'empty response'];
        }

        return [
            'ok' => true,
            'path' => $tempPath,
            'content_type' => $this->httpHeaderValue($headers, 'content-type'),
            'effective_url' => $url,
        ];
    }

    protected function downloadedImageInfo($path, $contentType = null)
    {
        $info = @getimagesize($path);
        if (!$info || empty($info['mime'])) {
            return ['ok' => false, 'reason' => 'not an image'];
        }

        $mime = $this->normalizeContentType($info['mime'] ?: $contentType);
        $extension = $this->imageExtensionForMime($mime);
        if (!$extension) {
            return ['ok' => false, 'reason' => 'unsupported image type ' . $mime];
        }

        return [
            'ok' => true,
            'mime' => $mime,
            'extension' => $extension,
        ];
    }

    protected function imageExtensionForMime($mime)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
        ];

        return $map[$this->normalizeContentType($mime)] ?? null;
    }

    protected function poiImageFilename(Poi $poi, $url, $extension)
    {
        $path = parse_url((string) $url, PHP_URL_PATH);
        $basename = $path ? rawurldecode(basename($path)) : '';
        $name = pathinfo($basename, PATHINFO_FILENAME);

        if (!$name || strtolower($name) === 'filepath') {
            $name = $poi->title ?: 'poi-' . $poi->id;
        }

        $slug = Str::slug($name) ?: 'poi-' . $poi->id;

        return substr($slug, 0, 180) . '.' . $extension;
    }

    protected function imageDownloadMaxBytes()
    {
        $maxMb = (int) env('POI_IMAGE_DOWNLOAD_MAX_MB', env('PICTURE_UPLOAD_MAX_MB', 10));

        return max(1, $maxMb) * 1024 * 1024;
    }

    protected function commonsImageWidth()
    {
        return max(320, (int) env('POI_COMMONS_IMAGE_WIDTH', 1600));
    }

    protected function normalizeContentType($contentType)
    {
        return strtolower(trim(explode(';', (string) $contentType)[0]));
    }

    protected function httpStatusFromHeaders(array $headers)
    {
        $status = 0;
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d+)/i', (string) $header, $matches)) {
                $status = (int) $matches[1];
            }
        }

        return $status ?: 200;
    }

    protected function httpHeaderValue(array $headers, $name)
    {
        $name = strtolower($name);
        $value = null;

        foreach ($headers as $header) {
            $parts = explode(':', (string) $header, 2);
            if (count($parts) === 2 && strtolower(trim($parts[0])) === $name) {
                $value = trim($parts[1]);
            }
        }

        return $value;
    }

    protected function writeImageSyncSkip($poiId, $reason)
    {
        if (!$this->isImageSyncVerbose()) {
            return;
        }

        $this->output->writeln(sprintf('  skipped #%d: %s', $poiId, $reason));
    }

    protected function isImageSyncVerbose()
    {
        return method_exists($this->output, 'isVerbose') && $this->output->isVerbose();
    }

    protected function shortImageSyncUrl($url)
    {
        $url = (string) $url;

        return strlen($url) > 120 ? substr($url, 0, 117) . '...' : $url;
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

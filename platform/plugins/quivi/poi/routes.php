<?php

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use System\Models\File as SystemFile;
use Quivi\Profile\Classes\JwtMiddleware;

function lmMockBadges()
{
    return [
        [
            'id' => 801,
            'code' => 'beauty-pioneer',
            'name' => 'Pioniere della bellezza',
            'category' => 'Aggiunta di nuovi posti',
            'descr' => 'Per il primo posto aggiunto',
            'picture' => 'https://picsum.photos/seed/lm-badge-beauty/320/320',
            'earned' => true,
            'earned_at' => '2026-05-01T10:30:00+02:00',
            'progress' => 1,
            'target' => 1,
        ],
        [
            'id' => 802,
            'code' => 'soul-cartographer',
            'name' => 'Cartografo dell Anima',
            'category' => 'Aggiunta di nuovi posti',
            'descr' => 'Per chi aggiunge piu di 10 luoghi',
            'picture' => 'https://picsum.photos/seed/lm-badge-cartographer/320/320',
            'earned' => true,
            'earned_at' => '2026-05-04T16:45:00+02:00',
            'progress' => 12,
            'target' => 10,
        ],
        [
            'id' => 803,
            'code' => 'art-megaphone',
            'name' => 'Megafono d arte',
            'category' => 'Condivisione',
            'descr' => 'Per la prima condivisione esterna',
            'picture' => 'https://picsum.photos/seed/lm-badge-megaphone/320/320',
            'earned' => true,
            'earned_at' => '2026-05-05T09:12:00+02:00',
            'progress' => 1,
            'target' => 1,
        ],
        [
            'id' => 804,
            'code' => 'inspiring-muse',
            'name' => 'Musa ispiratrice',
            'category' => 'Condivisione',
            'descr' => 'Per chi porta 5 nuovi amici sull app',
            'picture' => 'https://picsum.photos/seed/lm-badge-muse/320/320',
            'earned' => false,
            'earned_at' => null,
            'progress' => 3,
            'target' => 5,
        ],
    ];
}

function lmMockUserBadges($userId = 1)
{
    $badges = lmMockBadges();

    if ((int) $userId === 1) {
        return $badges;
    }

    return array_values(array_filter($badges, function ($badge) {
        return $badge['earned'];
    }));
}

function lmMockUsers()
{
    return [
        ['id' => 1, 'username' => 'giulia_art', 'avatar' => 'https://picsum.photos/seed/lm-user-1/160/160', 'badges' => lmMockUserBadges(1)],
        ['id' => 2, 'username' => 'milanowalks', 'avatar' => 'https://picsum.photos/seed/lm-user-2/160/160', 'badges' => lmMockUserBadges(2)],
        ['id' => 3, 'username' => 'archilover', 'avatar' => 'https://picsum.photos/seed/lm-user-3/160/160', 'badges' => lmMockUserBadges(3)],
        ['id' => 4, 'username' => 'museumdaily', 'avatar' => 'https://picsum.photos/seed/lm-user-4/160/160', 'badges' => lmMockUserBadges(4)],
    ];
}

function lmMockUser($id = 1)
{
    foreach (lmMockUsers() as $user) {
        if ((int) $user['id'] === (int) $id) {
            return $user;
        }
    }

    return lmMockUsers()[0];
}

function lmMockUserSummary($id = 1)
{
    $user = lmMockUser($id);
    unset($user['badges']);

    return $user;
}

function lmMockComments()
{
    return lmCommentsWithEngagement([
        [
            'id' => 1,
            'user' => lmMockUser(2),
            'comment_text' => 'La luce del cortile al tramonto e bellissima.',
            'comment_date' => '2026-05-05T18:24:00+02:00',
            'likes_num' => 3,
            'likes' => ['users' => [lmMockUser(1), lmMockUser(3), lmMockUser(4)]],
            'comments_num' => 1,
            'comments' => [
                [
                    'id' => 11,
                    'user' => lmMockUser(1),
                    'comment_text' => 'Confermo, vale la visita nel tardo pomeriggio.',
                    'comment_date' => '2026-05-05T19:01:00+02:00',
                    'likes_num' => 1,
                    'likes' => ['users' => [lmMockUser(2)]],
                    'comments_num' => 0,
                    'comments' => [],
                ],
            ],
        ],
        [
            'id' => 2,
            'user' => lmMockUser(3),
            'comment_text' => 'Consigliata la prenotazione nei weekend.',
            'comment_date' => '2026-05-04T11:42:00+02:00',
            'likes_num' => 2,
            'likes' => ['users' => [lmMockUser(1), lmMockUser(4)]],
            'comments_num' => 0,
            'comments' => [],
        ],
    ]);
}

function lmMockPictures()
{
    $pictures = [
        [
            'id' => 501,
            'poi_id' => 101,
            'user' => lmMockUser(1),
            'picture' => 'https://picsum.photos/seed/lm-duomo-detail/1200/900',
            'created_at' => '2026-05-06T09:15:00+02:00',
            'caption' => 'Dettaglio architettonico dal percorso di visita.',
            'exif_lat' => null,
            'exif_lng' => null,
            'likes_num' => 184,
            'bookmarks_num' => 42,
            'comments_num' => 12,
        ],
        [
            'id' => 502,
            'poi_id' => 102,
            'user' => lmMockUser(2),
            'picture' => 'https://picsum.photos/seed/lm-brera-gallery/1200/900',
            'created_at' => '2026-05-05T17:50:00+02:00',
            'caption' => 'Sala espositiva fotografata durante la visita.',
            'exif_lat' => null,
            'exif_lng' => null,
            'likes_num' => 92,
            'bookmarks_num' => 28,
            'comments_num' => 6,
        ],
        [
            'id' => 503,
            'poi_id' => 103,
            'user' => lmMockUser(3),
            'picture' => 'https://picsum.photos/seed/lm-sforza-castle/1200/900',
            'created_at' => '2026-05-04T14:20:00+02:00',
            'caption' => 'Scorcio del complesso monumentale.',
            'exif_lat' => null,
            'exif_lng' => null,
            'likes_num' => 126,
            'bookmarks_num' => 31,
            'comments_num' => 8,
        ],
    ];

    return array_map(function ($picture) {
        $picture['likes_num'] = lmPictureLikesNum($picture['id'], $picture['likes_num']);
        $picture['bookmarks_num'] = lmPictureBookmarksNum($picture['id'], $picture['bookmarks_num']);
        $picture['likes'] = [
            'users' => lmEngagementUsers(
                'quivi_poi_picture_likes',
                ['picture_id' => (int) $picture['id']],
                [lmMockUser(1), lmMockUser(2), lmMockUser(4)]
            ),
        ];
        $picture['comments'] = ['comments' => lmMockComments()];
        $picture['bookmarks'] = [
            'users' => lmEngagementUsers(
                'quivi_poi_bookmarks',
                ['target_type' => 'picture', 'target_id' => (int) $picture['id']],
                [lmMockUser(2), lmMockUser(3)]
            ),
        ];

        return $picture;
    }, $pictures);
}

function lmPoiSelectedColumns($includeRaw = false)
{
    $columns = [
        'id',
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
        'source_url',
        'license',
        'opening_hours',
        'tickets_info',
        'booking_info',
        'source',
        'source_file',
        'created_at',
        'updated_at',
    ];

    if ($includeRaw) {
        $columns[] = 'raw_data';
    }

    return $columns;
}

function lmPoiBaseQuery($includeRaw = false)
{
    return Db::table('quivi_poi_pois')
        ->select(lmPoiSelectedColumns($includeRaw))
        ->whereNull('deleted_at');
}

function lmPoiLimit(Request $request, $default = 100, $max = 500)
{
    $limit = (int) $request->input('limit', $default);

    if ($limit < 1) {
        return $default;
    }

    return min($limit, $max);
}

function lmPageParams(Request $request, $defaultPerPage = 100, $maxPerPage = 100)
{
    $perPage = (int) $request->input('per_page', $request->input('limit', $defaultPerPage));
    $perPage = max(1, min($perPage, $maxPerPage));
    $page    = max(1, (int) $request->input('page', 1));
    $offset  = ($page - 1) * $perPage;

    return compact('page', 'perPage', 'offset');
}

function lmPoiJsonDecode($value)
{
    if (is_array($value)) {
        return $value;
    }

    if (!$value || !is_string($value)) {
        return null;
    }

    $decoded = json_decode($value, true);

    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
}

function lmPoiText($value, $fallback = null)
{
    $value = is_string($value) ? trim($value) : $value;

    if ($value === null || $value === '') {
        return $fallback;
    }

    return $value;
}

function lmPoiCategoryDefinitions()
{
    return [
        'Monumento' => ['id' => 1, 'name_en' => 'Monument', 'name_it' => 'Monumento'],
        'Museo' => ['id' => 2, 'name_en' => 'Museum', 'name_it' => 'Museo'],
        'Sito Archeologico' => ['id' => 3, 'name_en' => 'Archaeological Site', 'name_it' => 'Sito Archeologico'],
        'Galleria' => ['id' => 4, 'name_en' => 'Gallery', 'name_it' => 'Galleria'],
        'Chiesa' => ['id' => 5, 'name_en' => 'Church', 'name_it' => 'Chiesa'],
        'Palazzo' => ['id' => 6, 'name_en' => 'Palace', 'name_it' => 'Palazzo'],
        'Architettura Civile' => ['id' => 7, 'name_en' => 'Civil Architecture', 'name_it' => 'Architettura Civile'],
        'Villa' => ['id' => 8, 'name_en' => 'Villa', 'name_it' => 'Villa'],
        'Architettura Fortificata' => ['id' => 9, 'name_en' => 'Fortified Architecture', 'name_it' => 'Architettura Fortificata'],
        'Infrastruttura Storica' => ['id' => 10, 'name_en' => 'Historic Infrastructure', 'name_it' => 'Infrastruttura Storica'],
        'Teatro' => ['id' => 11, 'name_en' => 'Theatre', 'name_it' => 'Teatro'],
        'Giardino Storico' => ['id' => 12, 'name_en' => 'Historic Garden', 'name_it' => 'Giardino Storico'],
        'Archivio' => ['id' => 13, 'name_en' => 'Archive', 'name_it' => 'Archivio'],
        'Biblioteca' => ['id' => 14, 'name_en' => 'Library', 'name_it' => 'Biblioteca'],
        'Sito UNESCO' => ['id' => 15, 'name_en' => 'UNESCO Site', 'name_it' => 'Sito UNESCO'],
        'Accademia' => ['id' => 16, 'name_en' => 'Academy', 'name_it' => 'Accademia'],
        'Altro' => ['id' => 99, 'name_en' => 'Other', 'name_it' => 'Altro'],
    ];
}

function lmPoiCategoryForName($categoryName, $type = null)
{
    $definitions = lmPoiCategoryDefinitions();
    $categoryName = lmPoiText($categoryName, lmPoiText($type, 'Altro'));

    if (isset($definitions[$categoryName])) {
        return $definitions[$categoryName];
    }

    foreach ($definitions as $knownName => $definition) {
        if (strcasecmp($knownName, $categoryName) === 0) {
            return $definition;
        }
    }

    return [
        'id' => 1000 + (crc32($categoryName) % 900000),
        'name_en' => $categoryName,
        'name_it' => $categoryName,
    ];
}

function lmPoiCategoryNameFromInput($category)
{
    if (is_array($category)) {
        $category = $category['name_it'] ?? $category['name'] ?? $category['id'] ?? reset($category);
    }

    if (is_numeric($category)) {
        foreach (lmPoiCategoryDefinitions() as $name => $definition) {
            if ((int) $definition['id'] === (int) $category) {
                return $name;
            }
        }
    }

    return (string) lmPoiText($category, 'Altro');
}

function lmPoiSchedaTypes()
{
    return ['base', 'unlockable', 'livemuseum', 'community'];
}

function lmPoiSchedaType($value)
{
    $value = strtolower((string) lmPoiText($value, 'base'));

    return in_array($value, lmPoiSchedaTypes(), true) ? $value : 'base';
}

function lmPoiFullAddress($fulladdress, $address, $city, $province, $region)
{
    if (lmPoiText($fulladdress)) {
        return trim($fulladdress);
    }

    $parts = array_filter([$address, $city, $province, $region], function ($part) {
        return lmPoiText($part) !== null;
    });

    return $parts ? implode(' - ', $parts) : null;
}

function lmPoiImageUrl($row)
{
    $imageUrl = lmPoiText($row->image_url ?? null);

    if ($imageUrl) {
        return $imageUrl;
    }

    return 'https://picsum.photos/seed/lm-poi-' . (int) $row->id . '/1200/900';
}

function lmPoiServices($categoryName)
{
    $services = [
        'Museo' => ['Bookshop', 'Guardaroba', 'Visite guidate'],
        'Monumento' => ['Visite guidate', 'Pannelli informativi'],
        'Sito Archeologico' => ['Percorso visita', 'Pannelli informativi'],
        'Chiesa' => ['Accesso libero', 'Visite guidate'],
        'Biblioteca' => ['Sala lettura', 'Consultazione cataloghi'],
        'Archivio' => ['Consultazione documenti', 'Sala studio'],
        'Teatro' => ['Biglietteria', 'Accesso eventi'],
        'Giardino Storico' => ['Percorso visita', 'Area verde'],
    ];

    return $services[$categoryName] ?? ['Informazioni in loco', 'Visite guidate'];
}

function lmPoiDayCode($day)
{
    $day = strtolower(trim((string) $day));

    if (strpos($day, 'lun') === 0) {
        return 'mon';
    }
    if (strpos($day, 'mar') === 0) {
        return 'tue';
    }
    if (strpos($day, 'mer') === 0) {
        return 'wed';
    }
    if (strpos($day, 'gio') === 0) {
        return 'thu';
    }
    if (strpos($day, 'ven') === 0) {
        return 'fri';
    }
    if (strpos($day, 'sab') === 0) {
        return 'sat';
    }
    if (strpos($day, 'dom') === 0) {
        return 'sun';
    }

    return null;
}

function lmPoiOpeningData($openingHours)
{
    $decoded = lmPoiJsonDecode($openingHours);
    $days = [];
    $hours = [];

    if (is_array($decoded)) {
        foreach ($decoded as $entry) {
            if (is_string($entry)) {
                $entry = ['testo' => $entry];
            }

            if (!is_array($entry)) {
                continue;
            }

            if (!empty($entry['testo'])) {
                foreach (explode('|', $entry['testo']) as $day) {
                    $code = lmPoiDayCode($day);
                    if ($code) {
                        $days[] = $code;
                    }
                }
            }

            if (!empty($entry['giorno'])) {
                $code = lmPoiDayCode($entry['giorno']);
                if ($code) {
                    $days[] = $code;
                }
            }

            if (!empty($entry['apertura']) && !empty($entry['chiusura'])) {
                $hours[] = trim($entry['apertura']) . '-' . trim($entry['chiusura']);
            }
        }
    }

    $days = array_values(array_unique($days));

    return [
        'opening_hours' => $decoded,
        'days_open' => $days ?: ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
        'hours_open' => $hours ? implode(', ', array_values(array_unique($hours))) : '09:00-18:00',
    ];
}

function lmPoiMetric($id, $base, $range)
{
    return $base + (((int) $id * 17) % $range);
}

function lmPictureUploadedFile(Request $request)
{
    return $request->file('picture') ?: $request->file('file');
}

function lmPictureUploadError($file)
{
    if (!$file || !$file->isValid()) {
        return 'Invalid upload.';
    }

    $maxSize = (int) env('PICTURE_UPLOAD_MAX_MB', 10) * 1024 * 1024;
    if ($file->getSize() > $maxSize) {
        return 'Picture exceeds maximum size.';
    }

    $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension()));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
        return 'Unsupported picture type.';
    }

    $mime = (string) $file->getMimeType();
    if ($mime !== '' && strpos($mime, 'image/') !== 0 && !in_array($extension, ['heic', 'heif'], true)) {
        return 'Unsupported picture type.';
    }

    return null;
}

function lmPictureSystemFileUrl($systemFile)
{
    $url = $systemFile->getPath();
    $assetBaseUrl = rtrim((string) env('PICTURE_ASSET_BASE_URL'), '/');
    if ($assetBaseUrl !== '') {
        $urlParts = parse_url($url);
        $url = $assetBaseUrl . ($urlParts['path'] ?? $url);
    }

    return $url;
}

function lmPictureStoreUpload($file)
{
    $systemFile = new SystemFile;
    $systemFile->is_public = true;
    $systemFile->fromPost($file);
    $systemFile->save();

    return [
        'file_id' => (int) $systemFile->id,
        'disk_name' => $systemFile->disk_name,
        'url' => lmPictureSystemFileUrl($systemFile),
        'exif' => lmPictureExifGps($systemFile->getLocalPath()),
    ];
}

function lmPictureSystemFileFromUrl($url)
{
    $path = parse_url((string) $url, PHP_URL_PATH);
    $diskName = $path ? basename($path) : null;

    if (!$diskName) {
        return null;
    }

    return SystemFile::where('disk_name', $diskName)->first();
}

function lmPictureExifGps($path)
{
    if (!function_exists('exif_read_data') || !is_file($path)) {
        return ['lat' => null, 'lng' => null];
    }

    $exif = @exif_read_data($path, 'GPS', true);
    $gps = is_array($exif) ? ($exif['GPS'] ?? $exif) : [];

    if (
        empty($gps['GPSLatitude']) ||
        empty($gps['GPSLatitudeRef']) ||
        empty($gps['GPSLongitude']) ||
        empty($gps['GPSLongitudeRef'])
    ) {
        return ['lat' => null, 'lng' => null];
    }

    return [
        'lat' => lmPictureExifCoordinate($gps['GPSLatitude'], $gps['GPSLatitudeRef']),
        'lng' => lmPictureExifCoordinate($gps['GPSLongitude'], $gps['GPSLongitudeRef']),
    ];
}

function lmPictureExifCoordinate($coordinate, $ref)
{
    if (!is_array($coordinate) || count($coordinate) < 3) {
        return null;
    }

    $degrees = lmPictureExifRational($coordinate[0]);
    $minutes = lmPictureExifRational($coordinate[1]);
    $seconds = lmPictureExifRational($coordinate[2]);

    if ($degrees === null || $minutes === null || $seconds === null) {
        return null;
    }

    $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

    if (in_array(strtoupper((string) $ref), ['S', 'W'], true)) {
        $decimal *= -1;
    }

    return round($decimal, 7);
}

function lmPictureExifRational($value)
{
    if (is_numeric($value)) {
        return (float) $value;
    }

    if (!is_string($value) || strpos($value, '/') === false) {
        return null;
    }

    [$numerator, $denominator] = array_pad(explode('/', $value, 2), 2, 0);
    $denominator = (float) $denominator;

    return $denominator == 0.0 ? null : ((float) $numerator / $denominator);
}

function lmPictureInputCoordinates(Request $request, array $uploadedExif = [])
{
    $gps = $request->input('exif_gps');
    if (is_string($gps)) {
        $decoded = json_decode($gps, true);
        $gps = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    $lat = $request->input('exif_lat', is_array($gps) ? ($gps['lat'] ?? null) : null);
    $lng = $request->input('exif_lng', is_array($gps) ? ($gps['lng'] ?? null) : null);

    if (($lat === null || $lat === '') && isset($uploadedExif['lat'])) {
        $lat = $uploadedExif['lat'];
    }

    if (($lng === null || $lng === '') && isset($uploadedExif['lng'])) {
        $lng = $uploadedExif['lng'];
    }

    return [
        'lat' => lmPictureCoordinateInput($lat),
        'lng' => lmPictureCoordinateInput($lng),
    ];
}

function lmPictureCoordinateInput($value)
{
    if ($value === null || $value === '') {
        return null;
    }

    return is_numeric($value) ? (float) $value : $value;
}

function lmPictureCoordinateError(array $coordinates)
{
    if ($coordinates['lat'] !== null && !is_numeric($coordinates['lat'])) {
        return 'exif_lat must be numeric.';
    }

    if ($coordinates['lng'] !== null && !is_numeric($coordinates['lng'])) {
        return 'exif_lng must be numeric.';
    }

    if ($coordinates['lat'] !== null && ($coordinates['lat'] < -90 || $coordinates['lat'] > 90)) {
        return 'exif_lat must be between -90 and 90.';
    }

    if ($coordinates['lng'] !== null && ($coordinates['lng'] < -180 || $coordinates['lng'] > 180)) {
        return 'exif_lng must be between -180 and 180.';
    }

    return null;
}

function lmPictureTableExists()
{
    static $exists = null;

    if ($exists === null) {
        $exists = Schema::hasTable('quivi_poi_pictures');
    }

    return $exists;
}

function lmPictureSystemFileColumnExists()
{
    static $exists = null;

    if ($exists === null) {
        $exists = lmPictureTableExists() && Schema::hasColumn('quivi_poi_pictures', 'system_file_id');
    }

    return $exists;
}

// ---------------------------------------------------------------------------
// User ranking helpers
// ---------------------------------------------------------------------------

function lmRankingTableExists(): bool
{
    static $exists = null;
    if ($exists === null) {
        $exists = Schema::hasTable('quivi_poi_user_rankings');
    }
    return $exists;
}

function lmUpdateUserRanking(int $userId, string $field, int $delta): void
{
    if (!$userId || !lmRankingTableExists()) {
        return;
    }

    $allowed = ['authority', 'photography'];
    if (!in_array($field, $allowed)) {
        return;
    }

    $row = Db::table('quivi_poi_user_rankings')->where('user_id', $userId)->first();

    if ($row) {
        $newValue = max(0, (int) $row->$field + $delta);
        Db::table('quivi_poi_user_rankings')
            ->where('user_id', $userId)
            ->update([$field => $newValue, 'updated_at' => date('Y-m-d H:i:s')]);
    } else {
        Db::table('quivi_poi_user_rankings')->insert([
            'user_id'    => $userId,
            'authority'  => $field === 'authority'   ? max(0, $delta) : 0,
            'photography'=> $field === 'photography' ? max(0, $delta) : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

function lmUserRanking(int $userId): array
{
    if (!lmRankingTableExists()) {
        return ['authority' => 0, 'photography' => 0];
    }

    $row = Db::table('quivi_poi_user_rankings')->where('user_id', $userId)->first();

    return [
        'authority'   => $row ? (int) $row->authority   : 0,
        'photography' => $row ? (int) $row->photography : 0,
    ];
}

// ---------------------------------------------------------------------------

function lmEngagementTableExists($table)
{
    static $exists = [];

    if (!array_key_exists($table, $exists)) {
        $exists[$table] = Schema::hasTable($table);
    }

    return $exists[$table];
}

function lmEngagementQuery($table, array $where)
{
    $query = Db::table($table);

    foreach ($where as $column => $value) {
        if ($value === null) {
            $query->whereNull($column);
        } else {
            $query->where($column, $value);
        }
    }

    return $query;
}

function lmEngagementCount($table, array $where)
{
    if (!lmEngagementTableExists($table)) {
        return 0;
    }

    return (int) lmEngagementQuery($table, $where)
        ->whereNull('deleted_at')
        ->count();
}

function lmEngagementUserIds($table, array $where, $limit = 20)
{
    if (!lmEngagementTableExists($table)) {
        return [];
    }

    return lmEngagementQuery($table, $where)
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->limit($limit)
        ->pluck('user_id')
        ->map(function ($userId) {
            return (int) $userId;
        })
        ->all();
}

function lmEngagementUsers($table, array $where, array $fallbackUsers = [])
{
    $users = [];
    $seen = [];

    foreach (lmEngagementUserIds($table, $where) as $userId) {
        $users[] = lmMockUser($userId);
        $seen[$userId] = true;
    }

    foreach ($fallbackUsers as $user) {
        $userId = (int) ($user['id'] ?? 0);
        if (!$userId || isset($seen[$userId])) {
            continue;
        }

        $users[] = $user;
        $seen[$userId] = true;
    }

    return $users;
}

function lmEngagementActivate($table, array $where, array $extra = [])
{
    if (!lmEngagementTableExists($table)) {
        return ['success' => false, 'created' => false, 'id' => null];
    }

    $active = lmEngagementQuery($table, $where)
        ->whereNull('deleted_at')
        ->first();

    if ($active) {
        if ($extra) {
            Db::table($table)->where('id', $active->id)->update(array_merge($extra, [
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
        }

        return ['success' => true, 'created' => false, 'id' => (int) $active->id];
    }

    $deleted = lmEngagementQuery($table, $where)
        ->orderBy('id', 'desc')
        ->first();

    $now = date('Y-m-d H:i:s');
    if ($deleted) {
        Db::table($table)->where('id', $deleted->id)->update(array_merge($extra, [
            'deleted_at' => null,
            'updated_at' => $now,
        ]));

        return ['success' => true, 'created' => true, 'id' => (int) $deleted->id];
    }

    $id = Db::table($table)->insertGetId(array_merge($where, $extra, [
        'created_at' => $now,
        'updated_at' => $now,
    ]));

    return ['success' => true, 'created' => true, 'id' => (int) $id];
}

function lmEngagementDeactivate($table, array $where)
{
    if (!lmEngagementTableExists($table)) {
        return false;
    }

    return (bool) lmEngagementQuery($table, $where)
        ->whereNull('deleted_at')
        ->update([
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
}

function lmPictureLikesNum($pictureId, $base)
{
    return (int) $base + lmEngagementCount('quivi_poi_picture_likes', ['picture_id' => (int) $pictureId]);
}

function lmPictureBookmarksNum($pictureId, $base)
{
    return (int) $base + lmEngagementCount('quivi_poi_bookmarks', [
        'target_type' => 'picture',
        'target_id' => (int) $pictureId,
    ]);
}

function lmPoiBookmarksNum($poiId, $base)
{
    return (int) $base + lmEngagementCount('quivi_poi_bookmarks', [
        'target_type' => 'poi',
        'target_id' => (int) $poiId,
    ]);
}

function lmCommentTableExists()
{
    static $exists = null;
    if ($exists === null) {
        $exists = Schema::hasTable('quivi_poi_comments');
    }
    return $exists;
}

const COMMENT_AUTOHIDE_REPORTS = 3;

function lmCommentLikesNum($commentId, $base = 0)
{
    return (int) $base + lmEngagementCount('quivi_poi_comment_likes', ['comment_id' => (int) $commentId]);
}

function lmCommentReportsTableExists()
{
    static $exists = null;
    if ($exists === null) {
        $exists = Schema::hasTable('quivi_poi_comment_reports');
    }
    return $exists;
}

function lmApplyCommentReport($commentId, $userId, $add)
{
    if (!lmCommentReportsTableExists()) {
        return false;
    }
    $commentId = (int) $commentId;
    $userId    = (int) $userId;

    $exists = Db::table('quivi_poi_comment_reports')
        ->where('comment_id', $commentId)
        ->where('user_id', $userId)
        ->exists();

    if ($add && !$exists) {
        Db::table('quivi_poi_comment_reports')->insert([
            'comment_id' => $commentId,
            'user_id'    => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $count = Db::table('quivi_poi_comments')->where('id', $commentId)->value('reports_count') + 1;
        $hidden = $count >= COMMENT_AUTOHIDE_REPORTS;
        Db::table('quivi_poi_comments')->where('id', $commentId)->update([
            'reports_count' => $count,
            'is_hidden'     => $hidden,
        ]);
        return ['reported' => true, 'reports_count' => $count, 'is_hidden' => $hidden];
    }

    if (!$add && $exists) {
        Db::table('quivi_poi_comment_reports')
            ->where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->delete();
        $count = max(0, Db::table('quivi_poi_comments')->where('id', $commentId)->value('reports_count') - 1);
        $hidden = $count >= COMMENT_AUTOHIDE_REPORTS;
        Db::table('quivi_poi_comments')->where('id', $commentId)->update([
            'reports_count' => $count,
            'is_hidden'     => $hidden,
        ]);
        return ['reported' => false, 'reports_count' => $count, 'is_hidden' => $hidden];
    }

    $current = Db::table('quivi_poi_comments')->where('id', $commentId)->first();
    return [
        'reported'      => $exists,
        'reports_count' => $current ? (int) $current->reports_count : 0,
        'is_hidden'     => $current ? (bool) $current->is_hidden : false,
    ];
}

function lmCommentResponse($row)
{
    $likesNum = lmCommentLikesNum((int) $row->id);
    $rating   = isset($row->rating) && $row->rating !== null ? (int) $row->rating : null;
    return [
        'id'           => (int) $row->id,
        'kind'         => $row->kind ?? 'comment',
        'parent_id'    => $row->parent_id ? (int) $row->parent_id : null,
        'target_type'  => $row->target_type,
        'target_id'    => (int) $row->target_id,
        'user'         => lmMockUser((int) $row->user_id),
        'comment_text' => $row->comment_text,
        'rating'       => $rating,
        'comment_date' => $row->created_at,
        'likes_num'    => $likesNum,
        'likes'         => ['users' => lmEngagementUsers('quivi_poi_comment_likes', ['comment_id' => (int) $row->id], [])],
        'comments_num'  => (int) Db::table('quivi_poi_comments')->where('parent_id', $row->id)->whereNull('deleted_at')->count(),
        'comments'      => [],
        'reports_count' => isset($row->reports_count) ? (int) $row->reports_count : 0,
        'is_hidden'     => isset($row->is_hidden) ? (bool) $row->is_hidden : false,
    ];
}

function lmStoredComments($targetType, $targetId, $parentId = null)
{
    if (!lmCommentTableExists()) {
        return [];
    }

    $q = Db::table('quivi_poi_comments')
        ->where('target_type', $targetType)
        ->where('target_id', (int) $targetId)
        ->whereNull('deleted_at')
        ->orderBy('created_at');

    if ($parentId === null) {
        $q->whereNull('parent_id');
    } else {
        $q->where('parent_id', (int) $parentId);
    }

    $rows = $q->get();
    $comments = [];

    foreach ($rows as $row) {
        $comment = lmCommentResponse($row);
        $replies = lmStoredComments($targetType, $targetId, $row->id);
        $comment['comments'] = $replies;
        $comment['comments_num'] = count($replies);
        $comments[] = $comment;
    }

    return $comments;
}

function lmCommentsWithEngagement(array $comments)
{
    foreach ($comments as &$comment) {
        $commentId = (int) ($comment['id'] ?? 0);
        if ($commentId) {
            $comment['likes_num'] = lmCommentLikesNum($commentId, $comment['likes_num'] ?? 0);
            $comment['likes'] = [
                'users' => lmEngagementUsers(
                    'quivi_poi_comment_likes',
                    ['comment_id' => $commentId],
                    $comment['likes']['users'] ?? []
                ),
            ];
        }

        if (!empty($comment['comments']) && is_array($comment['comments'])) {
            $comment['comments'] = lmCommentsWithEngagement($comment['comments']);
            $comment['comments_num'] = count($comment['comments']);
        }
    }
    unset($comment);

    return $comments;
}

function lmStoredPictureRows($limit = 50, $offset = 0, $poiId = null)
{
    if (!lmPictureTableExists()) {
        return [];
    }

    $q = Db::table('quivi_poi_pictures')->whereNull('deleted_at');

    if ($poiId !== null) {
        $q->where('poi_id', (int) $poiId);
    }

    return $q->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->limit($limit)
        ->offset($offset)
        ->get();
}

function lmStoredPictureCount($poiId = null)
{
    if (!lmPictureTableExists()) {
        return 0;
    }

    $q = Db::table('quivi_poi_pictures')->whereNull('deleted_at');

    if ($poiId !== null) {
        $q->where('poi_id', (int) $poiId);
    }

    return (int) $q->count();
}

function lmStoredPicturesForPoi($poiId, $limit = 2)
{
    if (!lmPictureTableExists()) {
        return [];
    }

    return Db::table('quivi_poi_pictures')
        ->where('poi_id', (int) $poiId)
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->limit($limit)
        ->get();
}

function lmStoredPicturesForUser($userId, $limit = 50)
{
    if (!lmPictureTableExists()) {
        return [];
    }

    return Db::table('quivi_poi_pictures')
        ->where('user_id', (int) $userId)
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->limit($limit)
        ->get();
}

function lmStoredPicture($id)
{
    if (!lmPictureTableExists()) {
        return null;
    }

    return Db::table('quivi_poi_pictures')
        ->where('id', (int) $id)
        ->whereNull('deleted_at')
        ->first();
}

function lmPictureResponse($row, $includePoi = false, $includeRelated = true, $poiSummary = null)
{
    $picture = [
        'id' => (int) $row->id,
        'poi_id' => (int) $row->poi_id,
        'user' => lmMockUser((int) ($row->user_id ?: 1)),
        'picture' => (string) $row->picture,
        'caption' => lmPoiText($row->caption ?? null),
        'exif_lat' => $row->exif_lat !== null ? (float) $row->exif_lat : null,
        'exif_lng' => $row->exif_lng !== null ? (float) $row->exif_lng : null,
        'created_at' => $row->created_at ? date('c', strtotime($row->created_at)) : null,
        'likes_num' => lmPictureLikesNum($row->id, lmPoiMetric($row->id, 12, 120)),
        'bookmarks_num' => lmPictureBookmarksNum($row->id, lmPoiMetric($row->id, 3, 40)),
        'comments_num' => lmPoiMetric($row->id, 1, 18),
    ];

    if ($includePoi) {
        $picture['poi'] = $poiSummary;
    }

    if ($includeRelated) {
        $picture['likes'] = [
            'users' => lmEngagementUsers(
                'quivi_poi_picture_likes',
                ['picture_id' => (int) $row->id],
                [lmMockUser(1), lmMockUser(2)]
            ),
        ];
        $picture['comments'] = ['comments' => lmMockComments()];
        $picture['bookmarks'] = [
            'users' => lmEngagementUsers(
                'quivi_poi_bookmarks',
                ['target_type' => 'picture', 'target_id' => (int) $row->id],
                [lmMockUser(3)]
            ),
        ];
    }

    return $picture;
}

function lmPictureResponses($rows, $includePoi = false, $includeRelated = true)
{
    $poiMap = [];

    if ($includePoi) {
        $ids = [];
        foreach ($rows as $row) {
            if (!empty($row->poi_id)) {
                $ids[] = (int) $row->poi_id;
            }
        }
        $poiMap = lmPoiSummariesByIds($ids);
    }

    $pictures = [];

    foreach ($rows as $row) {
        $poiSummary = $includePoi ? ($poiMap[(int) ($row->poi_id ?? 0)] ?? null) : null;
        $pictures[] = lmPictureResponse($row, $includePoi, $includeRelated, $poiSummary);
    }

    return $pictures;
}

function lmUserPictures($userId)
{
    $storedPictures = lmPictureResponses(lmStoredPicturesForUser($userId), false, true);
    $mockPictures = array_values(array_filter(lmMockPictures(), function ($picture) use ($userId) {
        return (int) $picture['user']['id'] === (int) $userId;
    }));

    return array_merge($storedPictures, $mockPictures);
}

function lmUniqueItemsById(array $items)
{
    $unique = [];
    $seen = [];

    foreach ($items as $item) {
        $id = (int) ($item['id'] ?? 0);
        if (!$id || isset($seen[$id])) {
            continue;
        }

        $unique[] = $item;
        $seen[$id] = true;
    }

    return $unique;
}

function lmMockBookmarkFolderDefinitions()
{
    return [
        ['id' => 701, 'name' => 'Weekend a Milano', 'bookmarks_num' => 3],
        ['id' => 702, 'name' => 'Arte moderna', 'bookmarks_num' => 1],
    ];
}

function lmStoredBookmarkFolders($userId)
{
    if (!lmEngagementTableExists('quivi_poi_bookmark_folders')) {
        return [];
    }

    return Db::table('quivi_poi_bookmark_folders')
        ->where('user_id', (int) $userId)
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->get();
}

function lmBookmarkFolderResponse($folder, $userId)
{
    $folderId  = is_object($folder) ? (int) $folder->id : (int) $folder['id'];
    $name      = is_object($folder) ? $folder->name : $folder['name'];
    $isPublic  = is_object($folder) ? (bool) ($folder->is_public ?? false) : (bool) ($folder['is_public'] ?? false);
    $baseCount = is_object($folder) ? 0 : (int) ($folder['bookmarks_num'] ?? 0);

    return [
        'id'            => $folderId,
        'name'          => $name,
        'is_public'     => $isPublic,
        'bookmarks_num' => $baseCount + lmEngagementCount('quivi_poi_bookmarks', [
            'user_id'   => (int) $userId,
            'folder_id' => $folderId,
        ]),
    ];
}

function lmBookmarkFolders($userId)
{
    $folders = [];

    foreach (lmStoredBookmarkFolders($userId) as $folder) {
        $folders[] = lmBookmarkFolderResponse($folder, $userId);
    }

    foreach (lmMockBookmarkFolderDefinitions() as $folder) {
        $folders[] = lmBookmarkFolderResponse($folder, $userId);
    }

    return lmUniqueItemsById($folders);
}

function lmBookmarkFolderExists($userId, $folderId)
{
    if (!$folderId) {
        return true;
    }

    if (lmEngagementTableExists('quivi_poi_bookmark_folders')) {
        $exists = Db::table('quivi_poi_bookmark_folders')
            ->where('id', (int) $folderId)
            ->where('user_id', (int) $userId)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return true;
        }
    }

    foreach (lmMockBookmarkFolderDefinitions() as $folder) {
        if ((int) $folder['id'] === (int) $folderId) {
            return true;
        }
    }

    return false;
}

function lmNormalizeBookmarkTarget(Request $request, $defaultType = null, $defaultId = null)
{
    $targetType = $defaultType ?: $request->input('target_type', $request->input('type'));
    $targetId = $defaultId ?: $request->input('target_id', $request->input('id'));

    if (!$targetType && $request->filled('poi_id')) {
        $targetType = 'poi';
        $targetId = $request->input('poi_id');
    }

    if (!$targetType && $request->filled('picture_id')) {
        $targetType = 'picture';
        $targetId = $request->input('picture_id');
    }

    $targetType = strtolower((string) $targetType);
    $aliases = [
        'pois'        => 'poi',
        'place'       => 'poi',
        'places'      => 'poi',
        'photo'       => 'picture',
        'photos'      => 'picture',
        'pictures'    => 'picture',
        'itineraries' => 'itinerary',
    ];

    return [
        'target_type' => $aliases[$targetType] ?? $targetType,
        'target_id' => $targetId === null || $targetId === '' ? null : (int) $targetId,
    ];
}

function lmBookmarkTarget($targetType, $targetId)
{
    if ($targetType === 'poi') {
        return lmMockPoi($targetId);
    }

    if ($targetType === 'picture') {
        return lmMockPicture($targetId);
    }

    if ($targetType === 'itinerary') {
        if (!lmItineraryTableExists()) {
            return null;
        }
        return Db::table('quivi_poi_itineraries')
            ->where('id', (int) $targetId)
            ->whereNull('deleted_at')
            ->first();
    }

    return null;
}

function lmBookmarkTargetCount($targetType, $targetId)
{
    if ($targetType === 'poi') {
        $poi = lmMockPoi($targetId);
        return $poi ? (int) $poi['num_bookmarks'] : 0;
    }

    if ($targetType === 'picture') {
        $picture = lmMockPicture($targetId);
        return $picture ? (int) $picture['bookmarks_num'] : 0;
    }

    if ($targetType === 'itinerary') {
        return lmEngagementCount('quivi_poi_bookmarks', [
            'target_type' => 'itinerary',
            'target_id'   => (int) $targetId,
        ]);
    }

    return 0;
}

function lmUserBookmarks($userId, $folderId = null)
{
    $pois       = [];
    $pictures   = [];
    $itineraries = [];

    if (lmEngagementTableExists('quivi_poi_bookmarks')) {
        $query = Db::table('quivi_poi_bookmarks')
            ->where('user_id', (int) $userId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($folderId !== null) {
            $query->where('folder_id', (int) $folderId);
        }

        foreach ($query->get() as $bookmark) {
            if ($bookmark->target_type === 'poi') {
                $poi = lmMockPoi($bookmark->target_id);
                if ($poi) {
                    $pois[] = $poi;
                }
                continue;
            }

            if ($bookmark->target_type === 'picture') {
                $picture = lmMockPicture($bookmark->target_id);
                if ($picture) {
                    $pictures[] = $picture;
                }
                continue;
            }

            if ($bookmark->target_type === 'itinerary' && lmItineraryTableExists()) {
                $row = Db::table('quivi_poi_itineraries')
                    ->where('id', (int) $bookmark->target_id)
                    ->whereNull('deleted_at')
                    ->first();
                if ($row) {
                    $itineraries[] = lmItineraryResponse($row);
                }
            }
        }
    }

    if ($folderId === null) {
        $pois     = array_merge($pois, [lmMockPoi(101), lmMockPoi(105)]);
        $pictures = array_merge($pictures, [lmMockPicture(502)]);
    } elseif ((int) $folderId === 701) {
        $pois     = array_merge($pois, [lmMockPoi(101), lmMockPoi(103)]);
        $pictures = array_merge($pictures, [lmMockPicture(501)]);
    } elseif ((int) $folderId === 702) {
        $pois     = array_merge($pois, [lmMockPoi(105)]);
        $pictures = array_merge($pictures, [lmMockPicture(502)]);
    }

    return [
        'pois'        => lmUniqueItemsById(array_values(array_filter($pois))),
        'pictures'    => lmUniqueItemsById(array_values(array_filter($pictures))),
        'itineraries' => lmUniqueItemsById(array_values(array_filter($itineraries))),
        'folders'     => $folderId === null ? lmBookmarkFolders($userId) : [],
    ];
}

function lmPoiRawImages(array $rawData)
{
    $images = [];

    foreach (['immagine', 'immagine_commons'] as $field) {
        if (!empty($rawData[$field]) && is_string($rawData[$field])) {
            $images[] = $rawData[$field];
        }
    }

    foreach (['immagini', 'immagini_iiif'] as $field) {
        if (empty($rawData[$field]) || !is_array($rawData[$field])) {
            continue;
        }

        foreach ($rawData[$field] as $image) {
            if (is_string($image)) {
                $images[] = $image;
                continue;
            }

            if (is_array($image)) {
                foreach (['url', 'src', 'image', 'immagine'] as $key) {
                    if (!empty($image[$key]) && is_string($image[$key])) {
                        $images[] = $image[$key];
                        break;
                    }
                }
            }
        }
    }

    return array_values(array_unique(array_filter($images)));
}

function lmPoiPictures($poi, array $rawData = [], $compact = false)
{
    if (!$compact) {
        $storedPictures = lmPictureResponses(lmStoredPicturesForPoi($poi['id'], 2), false, true);
        if ($storedPictures) {
            return $storedPictures;
        }
    }

    $images = array_values(array_unique(array_filter(array_merge(
        [$poi['image_url']],
        lmPoiRawImages($rawData)
    ))));

    if (!$images) {
        $images[] = 'https://picsum.photos/seed/lm-poi-' . (int) $poi['id'] . '-picture-1/1200/900';
    }

    $images = array_slice($images, 0, $compact ? 1 : 2);
    $pictures = [];

    foreach ($images as $index => $image) {
        $id = ((int) $poi['id'] * 10) + $index + 1;

        $pictures[] = [
            'id' => $id,
            'poi_id' => (int) $poi['id'],
            'user' => $compact
                ? lmMockUserSummary((((int) $poi['id'] + $index) % 4) + 1)
                : lmMockUser((((int) $poi['id'] + $index) % 4) + 1),
            'picture' => $image,
            'created_at' => date('c', strtotime('-' . (((int) $poi['id'] + $index) % 20) . ' days')),
            'caption' => null,
            'exif_lat' => null,
            'exif_lng' => null,
            'likes_num' => lmPictureLikesNum($id, lmPoiMetric($id, 12, 120)),
            'bookmarks_num' => lmPictureBookmarksNum($id, lmPoiMetric($id, 3, 40)),
            'comments_num' => lmPoiMetric($id, 1, 18),
            'likes' => [
                'users' => $compact
                    ? []
                    : lmEngagementUsers('quivi_poi_picture_likes', ['picture_id' => $id], [lmMockUser(1), lmMockUser(2)]),
            ],
            'comments' => ['comments' => $compact ? [] : lmMockComments()],
            'bookmarks' => [
                'users' => $compact
                    ? []
                    : lmEngagementUsers('quivi_poi_bookmarks', ['target_type' => 'picture', 'target_id' => $id], [lmMockUser(3)]),
            ],
        ];
    }

    return $pictures;
}

function lmPoiResponse($row, array $options = [])
{
    $rawData = lmPoiJsonDecode($row->raw_data ?? null) ?: [];
    $categoryName = lmPoiText($row->category_app ?? null, 'Altro');
    $category = lmPoiCategoryForName($categoryName, $row->type ?? null);
    $opening = lmPoiOpeningData($row->opening_hours ?? null);
    $title = lmPoiText($row->title ?? null, 'POI #' . (int) $row->id);
    $slug = lmPoiText($row->slug ?? null, lmPoiText($row->code ?? null, Str::slug($title) ?: 'poi-' . (int) $row->id));
    $imageUrl = lmPoiImageUrl($row);
    $numPictures = isset($rawData['num_immagini']) && is_numeric($rawData['num_immagini'])
        ? (int) $rawData['num_immagini']
        : lmPoiMetric($row->id, 1, 25);

    $poi = [
        'id' => (int) $row->id,
        'id_opendata' => lmPoiText($row->id_opendata ?? null, lmPoiText($row->code ?? null, 'LM.' . (int) $row->id)),
        'code' => lmPoiText($row->code ?? null, $slug),
        'slug' => $slug,
        'type' => lmPoiText($row->type ?? null, $categoryName),
        'category_app' => $categoryName,
        'schedaType' => lmPoiSchedaType($row->scheda_type ?? null),
        'title' => $title,
        'fulladdress' => lmPoiFullAddress($row->fulladdress ?? null, $row->address ?? null, $row->city ?? null, $row->province ?? null, $row->region ?? null),
        'address' => lmPoiText($row->address ?? null),
        'region' => lmPoiText($row->region ?? null),
        'province' => lmPoiText($row->province ?? null),
        'city' => lmPoiText($row->city ?? null),
        'zipcode' => lmPoiText($row->zipcode ?? null),
        'lat' => $row->lat !== null ? (float) $row->lat : null,
        'lng' => $row->lng !== null ? (float) $row->lng : null,
        'distance' => isset($row->distance) ? (int) round((float) $row->distance) : null,
        'category' => $category,
        'descr' => lmPoiText($row->descr ?? null, ''),
        'image_url' => $imageUrl,
        'phone' => lmPoiText($row->phone ?? null),
        'email' => lmPoiText($row->email ?? null),
        'website' => lmPoiText($row->website ?? null),
        'services' => lmPoiServices($category['name_it']),
        'num_pictures' => $numPictures,
        'num_likes' => lmPoiMetric($row->id, 60, 1800),
        'num_bookmarks' => lmPoiBookmarksNum($row->id, lmPoiMetric($row->id, 10, 400)),
        'num_comments' => lmPoiMetric($row->id, 2, 120),
        'days_open' => $opening['days_open'],
        'hours_open' => $opening['hours_open'],
        'opening_hours' => $opening['opening_hours'],
        'tickets_info' => lmPoiText($row->tickets_info ?? null, 'Informazioni biglietti non disponibili.'),
        'booking_info' => lmPoiText($row->booking_info ?? null, 'Informazioni prenotazione non disponibili.'),
        'source_url' => lmPoiText($row->source_url ?? null),
        'license' => lmPoiText($row->license ?? null),
        'source' => lmPoiText($row->source ?? null),
        'source_file' => lmPoiText($row->source_file ?? null),
    ];

    $related = $options['related'] ?? 'full';
    if ($related) {
        $compact = $related === 'compact';
        $pictures = lmPoiPictures($poi, $rawData, $compact);
        $poi['last_picture'] = $pictures[0] ?? null;
        $poi['pictures'] = $pictures;
        $poi['likes'] = ['users' => $compact ? [] : [lmMockUser(1), lmMockUser(2), lmMockUser(4)]];
        $poi['comments'] = ['comments' => $compact ? [] : lmMockComments()];
        $poi['bookmarks'] = [
            'users' => $compact
                ? []
                : lmEngagementUsers('quivi_poi_bookmarks', ['target_type' => 'poi', 'target_id' => (int) $row->id], [lmMockUser(2), lmMockUser(3)]),
        ];
    }

    return $poi;
}

function lmPoiSummary($row)
{
    $categoryName = lmPoiText($row->category_app ?? null, 'Altro');
    $category     = lmPoiCategoryForName($categoryName, $row->type ?? null);
    $slug         = lmPoiText($row->slug ?? null, lmPoiText($row->code ?? null, 'poi-' . (int) $row->id));

    return [
        'id'            => (int) $row->id,
        'slug'          => $slug,
        'title'         => lmPoiText($row->title ?? null, 'POI #' . (int) $row->id),
        'type'          => lmPoiText($row->type ?? null, $categoryName),
        'category_app'  => $categoryName,
        'category'      => $category,
        'schedaType'    => lmPoiSchedaType($row->scheda_type ?? null),
        'city'          => lmPoiText($row->city ?? null),
        'province'      => lmPoiText($row->province ?? null),
        'lat'           => $row->lat !== null ? (float) $row->lat : null,
        'lng'           => $row->lng !== null ? (float) $row->lng : null,
        'image_url'     => lmPoiImageUrl($row),
        'num_pictures'  => lmPoiMetric($row->id, 1, 25),
        'num_likes'     => lmPoiMetric($row->id, 60, 1800),
        'num_bookmarks' => lmPoiBookmarksNum($row->id, lmPoiMetric($row->id, 10, 400)),
        'num_comments'  => lmPoiMetric($row->id, 2, 120),
    ];
}

function lmPoiSummariesByIds(array $ids)
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

    if (!$ids) {
        return [];
    }

    $rows = lmPoiBaseQuery(false)->whereIn('id', $ids)->get();
    $map  = [];

    foreach ($rows as $row) {
        $map[(int) $row->id] = lmPoiSummary($row);
    }

    return $map;
}

function lmPoiResponses($rows, array $options = [])
{
    $pois = [];

    foreach ($rows as $row) {
        $pois[] = lmPoiResponse($row, $options);
    }

    return $pois;
}

function lmMockPois()
{
    return lmPoiResponses(
        lmPoiBaseQuery(false)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->orderBy('id')
            ->limit(100)
            ->get(),
        ['related' => 'full']
    );
}

function lmCategoriesForServices(array $services)
{
    if (!$services) {
        return [];
    }

    $map = [
        'Museo'             => ['Bookshop', 'Guardaroba', 'Visite guidate'],
        'Monumento'         => ['Visite guidate', 'Pannelli informativi'],
        'Sito Archeologico' => ['Percorso visita', 'Pannelli informativi'],
        'Chiesa'            => ['Accesso libero', 'Visite guidate'],
        'Biblioteca'        => ['Sala lettura', 'Consultazione cataloghi'],
        'Archivio'          => ['Consultazione documenti', 'Sala studio'],
        'Teatro'            => ['Biglietteria', 'Accesso eventi'],
        'Giardino Storico'  => ['Percorso visita', 'Area verde'],
    ];

    $matching = [];

    foreach ($map as $category => $catServices) {
        foreach ($services as $service) {
            if (in_array($service, $catServices, true)) {
                $matching[] = $category;
                break;
            }
        }
    }

    return array_values(array_unique($matching));
}

function lmPoiQueryMeta($query)
{
    $types = (clone $query)
        ->select('type')
        ->whereNotNull('type')
        ->where('type', '<>', '')
        ->distinct()
        ->orderBy('type')
        ->pluck('type')
        ->toArray();

    $categories = (clone $query)
        ->select('category_app')
        ->whereNotNull('category_app')
        ->where('category_app', '<>', '')
        ->distinct()
        ->pluck('category_app')
        ->toArray();

    $services = [];
    foreach ($categories as $cat) {
        foreach (lmPoiServices($cat) as $service) {
            $services[$service] = true;
        }
    }
    ksort($services);

    return [
        'types'    => array_values($types),
        'services' => array_keys($services),
    ];
}

function lmApplyPoiFilters($query, Request $request)
{
    if ($request->filled('category')) {
        $query->where('category_app', $request->input('category'));
    }

    $schedaType = $request->input('schedaType', $request->input('scheda_type'));
    if ($schedaType) {
        $query->where('scheda_type', $schedaType);
    }

    if ($request->filled('type')) {
        $query->where('type', $request->input('type'));
    }

    $services = $request->input('services');
    if ($services) {
        if (is_string($services)) {
            $services = array_filter(array_map('trim', explode(',', $services)));
        }
        $categories = lmCategoriesForServices(array_values((array) $services));
        if ($categories) {
            $query->whereIn('category_app', $categories);
        }
    }

    if ($request->filled('location')) {
        $location = $request->input('location');
        $query->where(function ($q) use ($location) {
            $q->where('city', $location)
              ->orWhere('province', $location)
              ->orWhere('region', $location);
        });
    }

    return $query;
}

function lmParseBbox($bbox)
{
    $parts = array_map('floatval', explode(',', (string) $bbox));
    if (count($parts) !== 4) {
        return null;
    }
    [$south, $west, $north, $east] = $parts;
    if ($south >= $north || $west >= $east) {
        return null;
    }
    return compact('south', 'west', 'north', 'east');
}

function lmZoomCellDeg(int $zoom): float
{
    $map = [
        1 => 20.0, 2 => 15.0, 3 => 10.0, 4 => 8.0,
        5 => 5.0,  6 => 3.0,  7 => 2.0,  8 => 1.0,
        9 => 0.5, 10 => 0.25, 11 => 0.1, 12 => 0.05,
        13 => 0.025, 14 => 0.01,
    ];
    return $map[max(1, min($zoom, 14))] ?? 0.01;
}

function lmBboxPoisQuery(array $bbox, Request $request = null)
{
    $q = lmPoiBaseQuery(false)
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->whereBetween('lat', [$bbox['south'], $bbox['north']])
        ->whereBetween('lng', [$bbox['west'], $bbox['east']]);

    if ($request) {
        lmApplyPoiFilters($q, $request);
    }

    return $q;
}

function lmNearbyPois($lat, $lng, $limit = 100, Request $request = null)
{
    $lat = (float) $lat;
    $lng = (float) $lng;
    $distanceSql = 'ROUND(6371000 * ACOS(LEAST(1, GREATEST(-1, COS(RADIANS(' . $lat . ')) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS(' . $lng . ')) + SIN(RADIANS(' . $lat . ')) * SIN(RADIANS(lat)))))) AS distance';

    $q = lmPoiBaseQuery(false)
        ->addSelect(Db::raw($distanceSql))
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->orderBy('distance')
        ->limit($limit);

    if ($request) {
        lmApplyPoiFilters($q, $request);
    }

    return lmPoiResponses($q->get(), ['related' => 'full']);
}

function lmMockPoi($identifier)
{
    $identifier = trim((string) $identifier);
    $query = lmPoiBaseQuery(true);

    if (ctype_digit($identifier)) {
        $query->where('id', (int) $identifier);
    } else {
        $query->where(function ($subQuery) use ($identifier) {
            $subQuery
                ->where('slug', $identifier)
                ->orWhere('code', $identifier)
                ->orWhere('id_opendata', $identifier);
        });
    }

    $row = $query->first();

    return $row ? lmPoiResponse($row, ['related' => 'full']) : null;
}

function lmSearchPoisQuery($query, Request $request = null)
{
    $like = '%' . str_replace(['%', '_'], ['\%', '\_'], trim((string) $query)) . '%';

    $q = lmPoiBaseQuery(false)
        ->where(function ($sub) use ($like) {
            $sub->where('title', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere('category_app', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('province', 'like', $like)
                ->orWhere('region', 'like', $like)
                ->orWhere('fulladdress', 'like', $like);
        });

    if ($request) {
        lmApplyPoiFilters($q, $request);
    }

    return $q;
}

function lmMockSearchPois($query, $limit = 100, $offset = 0, Request $request = null)
{
    if (trim((string) $query) === '') {
        return [];
    }

    $rows = lmSearchPoisQuery($query, $request)
        ->orderBy('title')
        ->limit($limit)
        ->offset($offset)
        ->get();

    return lmPoiResponses($rows, ['related' => 'full']);
}

function lmSearchPoisCount($query, Request $request = null)
{
    if (trim((string) $query) === '') {
        return 0;
    }

    return (int) lmSearchPoisQuery($query, $request)->count();
}

function lmPoiCategories()
{
    return Cache::remember('lm_poi_categories', 3600, function () {
        $rows = Db::table('quivi_poi_pois')
            ->select('category_app', Db::raw('COUNT(*) AS pois_count'))
            ->whereNull('deleted_at')
            ->whereNotNull('category_app')
            ->where('category_app', '<>', '')
            ->groupBy('category_app')
            ->orderBy('category_app')
            ->get();
        $categories = [];

        foreach ($rows as $row) {
            $category = lmPoiCategoryForName($row->category_app);
            $category['count'] = (int) $row->pois_count;
            $categories[] = $category;
        }

        return $categories ?: array_values(lmPoiCategoryDefinitions());
    });
}

function lmPoiUniqueSlug($title)
{
    $baseSlug = Str::slug($title) ?: 'poi';
    $slug = $baseSlug;
    $index = 2;

    while (Db::table('quivi_poi_pois')->where('slug', $slug)->exists()) {
        $slug = $baseSlug . '-' . $index;
        $index++;
    }

    return $slug;
}

function lmMockPicture($id)
{
    $storedPicture = lmStoredPicture($id);
    if ($storedPicture) {
        $picture = lmPictureResponse($storedPicture, true, true);
        unset($picture['poi_id']);

        return $picture;
    }

    foreach (lmMockPictures() as $picture) {
        if ((int) $picture['id'] === (int) $id) {
            $picture['poi'] = lmMockPoi($picture['poi_id']);
            unset($picture['poi_id']);

            return $picture;
        }
    }

    if ((int) $id > 10) {
        $poiId = intdiv(((int) $id) - 1, 10);
        $poi = $poiId > 0 ? lmMockPoi($poiId) : null;

        if ($poi && !empty($poi['pictures'])) {
            foreach ($poi['pictures'] as $picture) {
                if ((int) $picture['id'] === (int) $id) {
                    $picture['poi'] = $poi;
                    unset($picture['poi_id']);

                    return $picture;
                }
            }
        }
    }

    return null;
}

function lmMockCurrentUserId(Request $request)
{
    $user = $request->attributes->get('api_user');

    return $user ? (int) $user->id : (int) $request->input('user_id', 1);
}

function lmFindCommentInList($id, array $comments)
{
    foreach ($comments as $comment) {
        if ((int) ($comment['id'] ?? 0) === (int) $id) {
            return $comment;
        }

        if (!empty($comment['comments']) && is_array($comment['comments'])) {
            $nested = lmFindCommentInList($id, $comment['comments']);
            if ($nested) {
                return $nested;
            }
        }
    }

    return null;
}

function lmMockComment($id)
{
    return lmFindCommentInList($id, lmMockComments());
}

function lmPictureLikePayload($pictureId, $liked)
{
    $picture = lmMockPicture($pictureId);

    return [
        'success' => true,
        'picture_id' => (int) $pictureId,
        'liked' => (bool) $liked,
        'likes_num' => $picture ? (int) $picture['likes_num'] : lmPictureLikesNum($pictureId, 0),
    ];
}

function lmSetPictureLike(Request $request, $pictureId, $liked)
{
    $pictureId = (int) $pictureId;

    if ($pictureId <= 0 || !lmMockPicture($pictureId)) {
        return Response::json(['error' => 'Picture not found.'], 404);
    }

    $likerId = (int) $request->attributes->get('api_user')->id;

    $where = [
        'picture_id' => $pictureId,
        'user_id'    => $likerId,
    ];

    if ($liked) {
        $result = lmEngagementActivate('quivi_poi_picture_likes', $where);
    } else {
        lmEngagementDeactivate('quivi_poi_picture_likes', $where);
        $result = ['created' => false];
    }

    // Update photography ranking for the picture owner
    $picture = Db::table('quivi_poi_pictures')->select('user_id')->where('id', $pictureId)->first();
    if ($picture && (int) $picture->user_id && (int) $picture->user_id !== $likerId) {
        lmUpdateUserRanking((int) $picture->user_id, 'photography', $liked ? 1 : -1);
    }

    return Response::json(lmPictureLikePayload($pictureId, $liked), $liked && !empty($result['created']) ? 201 : 200);
}

function lmCommentLikePayload($commentId, $liked)
{
    $comment = lmMockComment($commentId);

    return [
        'success' => true,
        'comment_id' => (int) $commentId,
        'liked' => (bool) $liked,
        'likes_num' => $comment ? (int) $comment['likes_num'] : lmCommentLikesNum($commentId, 0),
    ];
}

function lmSetCommentLike(Request $request, $commentId, $liked)
{
    $commentId = (int) $commentId;

    if ($commentId <= 0) {
        return Response::json(['error' => 'Comment not found.'], 404);
    }

    $likerId = (int) $request->attributes->get('api_user')->id;

    $where = [
        'comment_id' => $commentId,
        'user_id'    => $likerId,
    ];

    if ($liked) {
        $result = lmEngagementActivate('quivi_poi_comment_likes', $where);
    } else {
        lmEngagementDeactivate('quivi_poi_comment_likes', $where);
        $result = ['created' => false];
    }

    // Update authority ranking for the comment author
    $comment = Db::table('quivi_poi_comments')->select('user_id')->where('id', $commentId)->first();
    if ($comment && (int) $comment->user_id && (int) $comment->user_id !== $likerId) {
        lmUpdateUserRanking((int) $comment->user_id, 'authority', $liked ? 1 : -1);
    }

    return Response::json(lmCommentLikePayload($commentId, $liked), $liked && !empty($result['created']) ? 201 : 200);
}

function lmSetBookmark(Request $request, $bookmarked, $defaultType = null, $defaultId = null)
{
    $target = lmNormalizeBookmarkTarget($request, $defaultType, $defaultId);

    $validator = Validator::make(array_merge($request->all(), $target), [
        'target_type' => 'required|string|in:poi,picture,itinerary',
        'target_id'   => 'required|integer|min:1',
        'folder_id'   => 'nullable|integer',
    ]);

    if ($validator->fails()) {
        return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
    }

    if (!lmBookmarkTarget($target['target_type'], $target['target_id'])) {
        return Response::json(['error' => 'Bookmark target not found.'], 404);
    }

    $userId = (int) $request->attributes->get('api_user')->id;
    $folderId = $request->filled('folder_id') ? (int) $request->input('folder_id') : null;

    if (!lmBookmarkFolderExists($userId, $folderId)) {
        return Response::json(['error' => 'Bookmark folder not found.'], 404);
    }

    $where = [
        'user_id' => $userId,
        'target_type' => $target['target_type'],
        'target_id' => $target['target_id'],
    ];

    if ($bookmarked) {
        $result = lmEngagementActivate('quivi_poi_bookmarks', $where, ['folder_id' => $folderId]);
    } else {
        lmEngagementDeactivate('quivi_poi_bookmarks', $where);
        $result = ['created' => false];
    }

    return Response::json([
        'success' => true,
        'bookmarked' => (bool) $bookmarked,
        'target_type' => $target['target_type'],
        'target_id' => $target['target_id'],
        'folder_id' => $bookmarked ? $folderId : null,
        'bookmarks_num' => lmBookmarkTargetCount($target['target_type'], $target['target_id']),
    ], $bookmarked && !empty($result['created']) ? 201 : 200);
}

function lmPoiApiKeyError(Request $request)
{
    $configuredApiKey = (string) env('POIS_API_KEY');

    if ($configuredApiKey === '') {
        return Response::json(['error' => 'POIS_API_KEY is not configured.'], 500);
    }

    $providedApiKey = $request->headers->get('X-API-KEY') ?: $request->input('api_key');

    if (!$providedApiKey) {
        return Response::json(['error' => 'API key missing.'], 401);
    }

    if (!hash_equals($configuredApiKey, (string) $providedApiKey)) {
        return Response::json(['error' => 'Invalid API key.'], 403);
    }

    return null;
}

Route::group(['prefix' => 'api/v1/pois'], function () {
    Route::get('all', function (Request $request) {
        if ($error = lmPoiApiKeyError($request)) {
            return $error;
        }

        // Paginated mode: ?page=N[&per_page=N]
        if ($request->filled('page')) {
            $validator = Validator::make($request->all(), [
                'page'       => 'integer|min:1',
                'per_page'   => 'nullable|integer|min:1|max:500',
                'limit'      => 'nullable|integer|min:1|max:500',
                'category'   => 'nullable|string',
                'schedaType' => 'nullable|string|in:base,unlockable,livemuseum,community',
                'scheda_type'=> 'nullable|string|in:base,unlockable,livemuseum,community',
            ]);

            if ($validator->fails()) {
                return Response::json(['errors' => $validator->errors()], 422);
            }

            ['page' => $page, 'perPage' => $perPage, 'offset' => $offset] = lmPageParams($request, 100, 500);

            $filterParams = $request->only(['category', 'schedaType', 'scheda_type', 'type', 'services', 'location']);
            $countCacheKey = 'lm_poi_all_count_' . md5(serialize($filterParams));
            $total = (int) Cache::remember($countCacheKey, 120, function () use ($request) {
                return (int) lmApplyPoiFilters(lmPoiBaseQuery(false), $request)->count();
            });

            $rows = lmPoiBaseQuery(false);
            lmApplyPoiFilters($rows, $request);
            $rows  = $rows->orderBy('id')->limit($perPage)->offset($offset)->get();
            $items = lmPoiResponses($rows, ['related' => 'compact']);

            return Response::json([
                'data' => $items,
                'meta' => [
                    'total'     => $total,
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                    'has_more'  => ($offset + count($items)) < $total,
                    'paginated' => true,
                ],
            ]);
        }

        // Legacy streaming mode: returns all records (no filters applied)
        $total = (int) Cache::remember('lm_poi_total_count', 120, function () {
            return (int) Db::table('quivi_poi_pois')->whereNull('deleted_at')->count();
        });

        return Response::stream(function () use ($total) {
            set_time_limit(0);
            $first = true;

            echo '{"data":[';

            lmPoiBaseQuery(false)
                ->orderBy('id')
                ->chunk(500, function ($rows) use (&$first) {
                    foreach ($rows as $row) {
                        if (!$first) {
                            echo ',';
                        }

                        echo json_encode(lmPoiResponse($row, ['related' => 'compact']), JSON_UNESCAPED_SLASHES);
                        $first = false;
                    }

                    flush();
                });

            echo '],"meta":{"total":' . $total . ',"paginated":false}}';
        }, 200, ['Content-Type' => 'application/json']);
    });

    Route::get('list', function (Request $request) {
        $hasBbox = $request->filled('bbox');

        $validator = Validator::make($request->all(), [
            'lat'        => ($hasBbox ? 'nullable' : 'required') . '|numeric',
            'lng'        => ($hasBbox ? 'nullable' : 'required_without:lon') . '|numeric',
            'lon'        => ($hasBbox ? 'nullable' : 'required_without:lng') . '|numeric',
            'bbox'       => 'nullable|string',
            'limit'      => 'nullable|integer|min:1|max:500',
            'category'   => 'nullable|string',
            'schedaType' => 'nullable|string|in:base,unlockable,livemuseum,community',
            'scheda_type'=> 'nullable|string|in:base,unlockable,livemuseum,community',
            'type'       => 'nullable|string',
            'services'   => 'nullable',
            'location'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        if ($hasBbox) {
            $bbox = lmParseBbox($request->input('bbox'));
            if (!$bbox) {
                return Response::json(['error' => 'Invalid bbox. Expected: south,west,north,east'], 422);
            }

            $limit = lmPoiLimit($request, 100, 500);
            $centerLat = ($bbox['south'] + $bbox['north']) / 2;
            $centerLng = ($bbox['west'] + $bbox['east']) / 2;
            $distanceSql = 'ROUND(6371000 * ACOS(LEAST(1, GREATEST(-1, COS(RADIANS(' . $centerLat . ')) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS(' . $centerLng . ')) + SIN(RADIANS(' . $centerLat . ')) * SIN(RADIANS(lat)))))) AS distance';

            $rows = lmBboxPoisQuery($bbox, $request)
                ->addSelect(Db::raw($distanceSql))
                ->orderBy('distance')
                ->limit($limit)
                ->get();

            return Response::json(['data' => lmPoiResponses($rows, ['related' => 'compact'])]);
        }

        $lng = $request->input('lng', $request->input('lon'));

        return Response::json([
            'data' => lmNearbyPois($request->input('lat'), $lng, lmPoiLimit($request), $request),
        ]);
    });

    Route::get('categories', function () {
        return Response::json(['data' => lmPoiCategories()]);
    });

    Route::get('cluster', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'bbox' => 'required|string',
            'zoom' => 'required|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $bbox = lmParseBbox($request->input('bbox'));
        if (!$bbox) {
            return Response::json(['error' => 'Invalid bbox. Expected: south,west,north,east'], 422);
        }

        $zoom    = (int) $request->input('zoom');
        $cellDeg = lmZoomCellDeg($zoom);

        $baseQuery = lmBboxPoisQuery($bbox, $request);
        $totalPois = (int) (clone $baseQuery)->count();

        if ($zoom >= 15) {
            $rows  = $baseQuery->orderBy('id')->limit(500)->get();
            $items = array_map(function ($row) {
                return [
                    'lat'       => (float) $row->lat,
                    'lng'       => (float) $row->lng,
                    'count'     => 1,
                    'id'        => (int) $row->id,
                    'slug'      => $row->slug,
                    'title'     => $row->title,
                    'type'      => $row->type,
                    'image_url' => $row->image_url,
                ];
            }, $baseQuery->orderBy('id')->limit(500)->get()->all());

            return Response::json([
                'data' => $items,
                'meta' => ['total_pois' => $totalPois, 'zoom' => $zoom, 'cell_deg' => null],
            ]);
        }

        $cellSql = (string) $cellDeg;
        $clusters = $baseQuery
            ->select([
                Db::raw('AVG(lat) AS lat'),
                Db::raw('AVG(lng) AS lng'),
                Db::raw('COUNT(*) AS cnt'),
                Db::raw('MIN(id) AS rep_id'),
                Db::raw('ROUND(lat / ' . $cellSql . ') * ' . $cellSql . ' AS cell_lat'),
                Db::raw('ROUND(lng / ' . $cellSql . ') * ' . $cellSql . ' AS cell_lng'),
            ])
            ->groupBy('cell_lat', 'cell_lng')
            ->orderByRaw('cnt DESC')
            ->limit(500)
            ->get();

        $repIds   = $clusters->where('cnt', 1)->pluck('rep_id')->filter()->values()->toArray();
        $repPois  = [];
        if ($repIds) {
            $repPois = Db::table('quivi_poi_pois')
                ->select(['id', 'slug', 'title', 'type', 'image_url'])
                ->whereIn('id', $repIds)
                ->get()
                ->keyBy('id');
        }

        $items = $clusters->map(function ($cluster) use ($repPois) {
            $item = [
                'lat'   => round((float) $cluster->lat, 6),
                'lng'   => round((float) $cluster->lng, 6),
                'count' => (int) $cluster->cnt,
                'id'        => null,
                'slug'      => null,
                'title'     => null,
                'type'      => null,
                'image_url' => null,
            ];

            if ((int) $cluster->cnt === 1 && isset($repPois[$cluster->rep_id])) {
                $poi = $repPois[$cluster->rep_id];
                $item['id']        = (int) $poi->id;
                $item['slug']      = $poi->slug;
                $item['title']     = $poi->title;
                $item['type']      = $poi->type;
                $item['image_url'] = $poi->image_url;
            }

            return $item;
        })->values()->all();

        return Response::json([
            'data' => $items,
            'meta' => ['total_pois' => $totalPois, 'zoom' => $zoom, 'cell_deg' => $cellDeg],
        ]);
    });

    Route::get('search', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'q'          => 'nullable|string',
            'page'       => 'nullable|integer|min:1',
            'per_page'   => 'nullable|integer|min:1|max:500',
            'limit'      => 'nullable|integer|min:1|max:500',
            'category'   => 'nullable|string',
            'schedaType' => 'nullable|string|in:base,unlockable,livemuseum,community',
            'scheda_type'=> 'nullable|string|in:base,unlockable,livemuseum,community',
            'type'       => 'nullable|string',
            'services'   => 'nullable',
            'location'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $q = trim((string) $request->input('q', ''));
        ['page' => $page, 'perPage' => $perPage, 'offset' => $offset] = lmPageParams($request, 100, 500);

        $baseQuery = $q !== ''
            ? fn() => lmSearchPoisQuery($q, $request)
            : fn() => lmApplyPoiFilters(lmPoiBaseQuery(false), $request);

        $total       = (int) $baseQuery()->count();
        $filtersMeta = lmPoiQueryMeta($baseQuery());
        $items       = lmPoiResponses(
            $baseQuery()->orderBy('title')->limit($perPage)->offset($offset)->get(),
            ['related' => 'full']
        );

        return Response::json([
            'data' => $items,
            'meta' => [
                'total'     => $total,
                'page'      => $page,
                'per_page'  => $perPage,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'has_more'  => ($offset + count($items)) < $total,
                'filters'   => $filtersMeta,
            ],
        ]);
    });

    Route::post('create', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required_without:lon|numeric',
            'lon' => 'required_without:lng|numeric',
            'title' => 'required_without:name|string|between:2,255',
            'name' => 'required_without:title|string|between:2,255',
            'category' => 'required',
            'descr' => 'nullable|string',
            'image_url' => 'nullable',
            'picture' => 'nullable',
            'fulladdress' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'region' => 'nullable|string',
            'zipcode' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'website' => 'nullable|string',
            'tickets_info' => 'nullable|string',
            'booking_info' => 'nullable|string',
            'schedaType' => 'nullable|string|in:base,unlockable,livemuseum,community',
            'scheda_type' => 'nullable|string|in:base,unlockable,livemuseum,community',
            'source_url' => 'nullable|url|max:512',
            'license' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
        }

        $lat = (float) $request->input('lat');
        $lng = (float) $request->input('lng', $request->input('lon'));
        $title = $request->input('title', $request->input('name'));
        $categoryName = lmPoiCategoryNameFromInput($request->input('category'));
        $schedaType = lmPoiSchedaType($request->input('schedaType', $request->input('scheda_type', 'base')));
        $dedupeKey = sha1(strtolower($title) . '|' . $lat . '|' . $lng . '|' . strtolower((string) $request->input('city')));

        $existing = Db::table('quivi_poi_pois')
            ->where('dedupe_key', $dedupeKey)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return Response::json(['status' => 'DUPLICATE', 'id' => (int) $existing->id, 'poi' => lmMockPoi($existing->id)], 200);
        }

        $slug = lmPoiUniqueSlug($title);
        $now = date('Y-m-d H:i:s');
        $rawData = $request->all();
        $rawData['source'] = 'api/v1/pois/create';

        $data = [
            'id_opendata' => null,
            'code' => $slug,
            'title' => $title,
            'slug' => $slug,
            'type' => $categoryName,
            'category_app' => $categoryName,
            'scheda_type' => $schedaType,
            'fulladdress' => lmPoiFullAddress($request->input('fulladdress'), $request->input('address'), $request->input('city'), $request->input('province'), $request->input('region')),
            'address' => lmPoiText($request->input('address')),
            'city' => lmPoiText($request->input('city')),
            'province' => lmPoiText($request->input('province')),
            'region' => lmPoiText($request->input('region')),
            'zipcode' => lmPoiText($request->input('zipcode')),
            'lat' => $lat,
            'lng' => $lng,
            'descr' => lmPoiText($request->input('descr')),
            'image_url' => lmPoiText($request->input('image_url'), lmPoiText($request->input('picture'))),
            'phone' => lmPoiText($request->input('phone')),
            'email' => lmPoiText($request->input('email')),
            'website' => lmPoiText($request->input('website')),
            'source_url' => lmPoiText($request->input('source_url')),
            'license' => lmPoiText($request->input('license')),
            'opening_hours' => null,
            'tickets_info' => lmPoiText($request->input('tickets_info')),
            'booking_info' => lmPoiText($request->input('booking_info')),
            'source' => 'App',
            'source_file' => 'api/v1/pois/create',
            'dedupe_key' => $dedupeKey,
            'raw_data' => json_encode($rawData, JSON_UNESCAPED_SLASHES),
            'created_at' => $now,
            'updated_at' => $now,
            'imported_at' => $now,
        ];

        $data['location'] = Db::raw('ST_SRID(POINT(' . $lng . ', ' . $lat . '), 4326)');
        $id = Db::table('quivi_poi_pois')->insertGetId($data);

        return Response::json(['status' => 'OK', 'id' => $id, 'poi' => lmMockPoi($id)], 201);
    });

    Route::post('{identifier}/bookmark', function (Request $request, $identifier) {
        $poi = lmMockPoi($identifier);

        if (!$poi) {
            return Response::json(['error' => 'POI not found.'], 404);
        }

        return lmSetBookmark($request, true, 'poi', $poi['id']);
    });

    Route::delete('{identifier}/bookmark', function (Request $request, $identifier) {
        $poi = lmMockPoi($identifier);

        if (!$poi) {
            return Response::json(['error' => 'POI not found.'], 404);
        }

        return lmSetBookmark($request, false, 'poi', $poi['id']);
    });

    Route::get('{identifier}/view', function ($identifier) {
        $poi = lmMockPoi($identifier);

        if (!$poi) {
            return Response::json(['error' => 'POI not found.'], 404);
        }

        return Response::json($poi);
    });

    Route::get('{identifier}/pictures', function (Request $request, $identifier) {
        $poi = lmMockPoi($identifier);

        if (!$poi) {
            return Response::json(['error' => 'POI not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'sort'     => 'nullable|in:recent,top',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'limit'    => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $poiId = (int) $poi['id'];
        ['page' => $page, 'perPage' => $perPage, 'offset' => $offset] = lmPageParams($request);

        $total = lmStoredPictureCount($poiId);

        $q = lmPictureTableExists()
            ? Db::table('quivi_poi_pictures')
                ->where('poi_id', $poiId)
                ->whereNull('deleted_at')
            : null;

        if ($q) {
            if ($request->input('sort') === 'top') {
                $q->orderByRaw('(SELECT COUNT(*) FROM quivi_poi_picture_likes WHERE picture_id = quivi_poi_pictures.id) DESC');
            } else {
                $q->orderBy('created_at', 'desc')->orderBy('id', 'desc');
            }

            $rows = $q->limit($perPage)->offset($offset)->get();
        } else {
            $rows = [];
        }

        $items = lmPictureResponses($rows, false, true);

        // Fall back to the inline pictures from the POI response when the DB is empty
        if ($total === 0 && $page === 1) {
            $items = $poi['pictures'] ?? [];
            $total = count($items);
        }

        return Response::json([
            'data' => $items,
            'meta' => [
                'total'     => $total,
                'page'      => $page,
                'per_page'  => $perPage,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'has_more'  => ($offset + count($items)) < $total,
            ],
        ]);
    });
});

Route::group(['prefix' => 'api/v1/pictures'], function () {
    Route::get('list', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'poi_id'   => 'nullable|integer|min:1',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'limit'    => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $poiId = $request->filled('poi_id') ? (int) $request->input('poi_id') : null;
        ['page' => $page, 'perPage' => $perPage, 'offset' => $offset] = lmPageParams($request);

        $total  = lmStoredPictureCount($poiId);
        $rows   = lmStoredPictureRows($perPage, $offset, $poiId);

        $items = array_map(function ($picture) {
            unset($picture['poi_id'], $picture['likes'], $picture['comments'], $picture['bookmarks']);

            return $picture;
        }, lmPictureResponses($rows, true, false));

        // Append mock pictures only on page 1 when no poi_id filter is active and stored results are sparse
        if ($poiId === null && $page === 1 && count($items) < $perPage) {
            $mockPictures = lmMockPictures();
            $mockPoiIds   = array_column($mockPictures, 'poi_id');
            $mockPoiMap   = lmPoiSummariesByIds($mockPoiIds);

            $mockItems = array_map(function ($picture) use ($mockPoiMap) {
                $picture['poi'] = $mockPoiMap[(int) $picture['poi_id']] ?? null;
                unset($picture['poi_id'], $picture['likes'], $picture['comments'], $picture['bookmarks']);

                return $picture;
            }, $mockPictures);

            $items = array_merge($items, $mockItems);
            $total = max($total, count($items));
        }

        return Response::json([
            'data' => $items,
            'meta' => [
                'total'        => $total,
                'page'         => $page,
                'per_page'     => $perPage,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
                'has_more'     => ($offset + count($items)) < $total,
            ],
        ]);
    });

    Route::post('upload', function (Request $request) {
        $file = lmPictureUploadedFile($request);

        if (!$file) {
            return Response::json([
                'status' => 'KO',
                'errors' => ['picture' => ['The picture field is required.']],
            ], 422);
        }

        if ($error = lmPictureUploadError($file)) {
            return Response::json([
                'status' => 'KO',
                'errors' => ['picture' => [$error]],
            ], 422);
        }

        $uploaded = lmPictureStoreUpload($file);

        return Response::json([
            'status' => 'OK',
            'system_file_id' => $uploaded['file_id'],
            'url' => $uploaded['url'],
            'picture' => $uploaded['url'],
            'exif_lat' => $uploaded['exif']['lat'],
            'exif_lng' => $uploaded['exif']['lng'],
        ], 201);
    });

    Route::get('{id}/view', function ($id) {
        $picture = lmMockPicture($id);

        if (!$picture) {
            return Response::json(['error' => 'Picture not found.'], 404);
        }

        return Response::json($picture);
    });

    Route::group(['middleware' => [JwtMiddleware::class]], function () {
        Route::post('{id}/like', function (Request $request, $id) {
            return lmSetPictureLike($request, $id, true);
        });

        Route::delete('{id}/like', function (Request $request, $id) {
            return lmSetPictureLike($request, $id, false);
        });

        Route::post('{id}/bookmark', function (Request $request, $id) {
            return lmSetBookmark($request, true, 'picture', $id);
        });

        Route::delete('{id}/bookmark', function (Request $request, $id) {
            return lmSetBookmark($request, false, 'picture', $id);
        });
    });

    Route::post('create', function (Request $request) {
        $uploadedFile = lmPictureUploadedFile($request);
        $rules = [
            'poi_id' => 'required|integer',
            'user_id' => 'nullable|integer',
            'system_file_id' => 'nullable|integer',
            'caption' => 'nullable|string|max:500',
            'exif_lat' => 'nullable|numeric|between:-90,90',
            'exif_lng' => 'nullable|numeric|between:-180,180',
            'exif_gps.lat' => 'nullable|numeric|between:-90,90',
            'exif_gps.lng' => 'nullable|numeric|between:-180,180',
        ];

        if (!$uploadedFile) {
            $rules['picture'] = $request->filled('system_file_id') ? 'nullable|url' : 'required|url';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
        }

        if (!lmPictureTableExists()) {
            return Response::json(['error' => 'Picture storage is not available.'], 500);
        }

        $poiId = (int) $request->input('poi_id');
        if (!Db::table('quivi_poi_pois')->where('id', $poiId)->whereNull('deleted_at')->exists()) {
            return Response::json(['error' => 'POI not found.'], 404);
        }

        $pictureUrl = (string) $request->input('picture');
        $systemFileId = null;
        $uploadedExif = ['lat' => null, 'lng' => null];

        if ($uploadedFile) {
            if ($error = lmPictureUploadError($uploadedFile)) {
                return Response::json([
                    'status' => 'KO',
                    'errors' => ['picture' => [$error]],
                ], 422);
            }

            $uploaded = lmPictureStoreUpload($uploadedFile);
            $pictureUrl = $uploaded['url'];
            $systemFileId = $uploaded['file_id'];
            $uploadedExif = $uploaded['exif'];
        } elseif ($request->filled('system_file_id')) {
            $systemFile = SystemFile::find((int) $request->input('system_file_id'));
            if (!$systemFile) {
                return Response::json([
                    'status' => 'KO',
                    'errors' => ['system_file_id' => ['System file not found.']],
                ], 422);
            }

            $systemFileId = (int) $systemFile->id;
            if ($pictureUrl === '') {
                $pictureUrl = lmPictureSystemFileUrl($systemFile);
            }
        } elseif ($pictureUrl !== '') {
            $systemFile = lmPictureSystemFileFromUrl($pictureUrl);
            if ($systemFile) {
                $systemFileId = (int) $systemFile->id;
            }
        }

        $coordinates = lmPictureInputCoordinates($request, $uploadedExif);
        if ($error = lmPictureCoordinateError($coordinates)) {
            return Response::json([
                'status' => 'KO',
                'errors' => ['exif_gps' => [$error]],
            ], 422);
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'poi_id' => $poiId,
            'user_id' => lmMockCurrentUserId($request),
            'picture' => $pictureUrl,
            'caption' => lmPoiText($request->input('caption')),
            'exif_lat' => $coordinates['lat'],
            'exif_lng' => $coordinates['lng'],
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (lmPictureSystemFileColumnExists()) {
            $data['system_file_id'] = $systemFileId;
        }

        $id = Db::table('quivi_poi_pictures')->insertGetId($data);

        $picture = lmPictureResponse(lmStoredPicture($id), false, true);

        return Response::json(array_merge(['status' => 'OK'], $picture), 201);
    });

    Route::delete('{id}/delete', function (Request $request, $id) {
        $storedPicture = lmStoredPicture($id);
        if ($storedPicture) {
            if ((int) ($storedPicture->user_id ?: 1) !== lmMockCurrentUserId($request)) {
                return Response::json(['error' => 'Forbidden.'], 403);
            }

            $systemFileId = $storedPicture->system_file_id ?? null;

            Db::table('quivi_poi_pictures')
                ->where('id', (int) $id)
                ->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            if ($systemFileId) {
                $systemFile = SystemFile::find((int) $systemFileId);
                if ($systemFile) {
                    $systemFile->delete();
                }
            }

            return Response::json(['success' => true, 'id' => (int) $id]);
        }

        $picture = lmMockPicture($id);

        if (!$picture) {
            return Response::json(['error' => 'Picture not found.'], 404);
        }

        if ((int) $picture['user']['id'] !== lmMockCurrentUserId($request)) {
            return Response::json(['error' => 'Forbidden.'], 403);
        }

        return Response::json(['success' => true]);
    });
});

Route::group(['prefix' => 'api/v1/comments'], function () {
    Route::get('{id}/likes', function (Request $request, $id) {
        $commentId = (int) $id;
        if ($commentId <= 0) {
            return Response::json(['error' => 'Comment not found.'], 404);
        }
        $likesNum = lmCommentLikesNum($commentId);
        $users = lmEngagementUsers('quivi_poi_comment_likes', ['comment_id' => $commentId], []);
        return Response::json([
            'comment_id' => $commentId,
            'likes_num'  => $likesNum,
            'users'      => $users,
        ]);
    });

    Route::group(['middleware' => [JwtMiddleware::class]], function () {
        Route::post('{targetId}/create', function (Request $request, $targetId) {
            $validator = Validator::make($request->all(), [
                'comment_text' => 'required|string|between:1,1000',
                'target_type'  => 'nullable|string|in:poi,picture',
                'parent_id'    => 'nullable|integer|min:1',
                'kind'         => 'nullable|string|in:comment,review',
                'rating'       => 'nullable|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return Response::json(['errors' => $validator->errors()], 422);
            }

            $userId     = (int) $request->attributes->get('api_user')->id;
            $targetType = $request->input('target_type', 'poi');
            $parentId   = $request->input('parent_id') ? (int) $request->input('parent_id') : null;
            $now        = date('Y-m-d H:i:s');

            if (!lmCommentTableExists()) {
                return Response::json(['error' => 'Comments storage is not available.'], 500);
            }

            $kind   = $request->input('kind', 'comment');
            $rating = ($kind === 'review' && $request->filled('rating'))
                ? (int) $request->input('rating')
                : null;

            $id = Db::table('quivi_poi_comments')->insertGetId([
                'target_type'  => $targetType,
                'target_id'    => (int) $targetId,
                'parent_id'    => $parentId,
                'user_id'      => $userId,
                'comment_text' => $request->input('comment_text'),
                'kind'         => $kind,
                'rating'       => $rating,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            $row = Db::table('quivi_poi_comments')->where('id', $id)->first();

            return Response::json(lmCommentResponse($row), 201);
        });

        Route::post('{id}/like', function (Request $request, $id) {
            return lmSetCommentLike($request, $id, true);
        });

        Route::delete('{id}/like', function (Request $request, $id) {
            return lmSetCommentLike($request, $id, false);
        });

        Route::delete('{id}/delete', function (Request $request, $id) {
            if (!lmCommentTableExists()) {
                return Response::json(['error' => 'Comments storage is not available.'], 500);
            }

            $comment = Db::table('quivi_poi_comments')
                ->where('id', (int) $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$comment) {
                return Response::json(['error' => 'Comment not found.'], 404);
            }

            if ((int) $comment->user_id !== (int) $request->attributes->get('api_user')->id) {
                return Response::json(['error' => 'Forbidden.'], 403);
            }

            Db::table('quivi_poi_comments')
                ->where('id', (int) $id)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            return Response::json(['success' => true, 'id' => (int) $id]);
        });

        Route::match(['put', 'patch'], '{id}/update', function (Request $request, $id) {
            $validator = Validator::make($request->all(), [
                'comment_text' => 'required|string|between:1,1000',
            ]);

            if ($validator->fails()) {
                return Response::json(['errors' => $validator->errors()], 422);
            }

            if (!lmCommentTableExists()) {
                return Response::json(['error' => 'Comments storage is not available.'], 500);
            }

            $comment = Db::table('quivi_poi_comments')
                ->where('id', (int) $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$comment) {
                return Response::json(['error' => 'Comment not found.'], 404);
            }

            if ((int) $comment->user_id !== (int) $request->attributes->get('api_user')->id) {
                return Response::json(['error' => 'Forbidden.'], 403);
            }

            Db::table('quivi_poi_comments')
                ->where('id', (int) $id)
                ->update(['comment_text' => $request->input('comment_text'), 'updated_at' => date('Y-m-d H:i:s')]);

            $row = Db::table('quivi_poi_comments')->where('id', (int) $id)->first();

            return Response::json(lmCommentResponse($row));
        });

        Route::post('{id}/report', function (Request $request, $id) {
            $commentId = (int) $id;
            if ($commentId <= 0) {
                return Response::json(['error' => 'Comment not found.'], 404);
            }
            if (!lmCommentTableExists()) {
                return Response::json(['error' => 'Comments storage is not available.'], 500);
            }
            $comment = Db::table('quivi_poi_comments')->where('id', $commentId)->whereNull('deleted_at')->first();
            if (!$comment) {
                return Response::json(['error' => 'Comment not found.'], 404);
            }
            $userId = (int) $request->attributes->get('api_user')->id;
            $result = lmApplyCommentReport($commentId, $userId, true);
            return Response::json(array_merge(['comment_id' => $commentId], $result), 201);
        });

        Route::delete('{id}/report', function (Request $request, $id) {
            $commentId = (int) $id;
            if ($commentId <= 0) {
                return Response::json(['error' => 'Comment not found.'], 404);
            }
            if (!lmCommentTableExists()) {
                return Response::json(['error' => 'Comments storage is not available.'], 500);
            }
            $userId = (int) $request->attributes->get('api_user')->id;
            $result = lmApplyCommentReport($commentId, $userId, false);
            return Response::json(array_merge(['comment_id' => $commentId], $result));
        });
    });
});

Route::group(['prefix' => 'api/v1/bookmarks', 'middleware' => [JwtMiddleware::class]], function () {
    Route::post('create', function (Request $request) {
        return lmSetBookmark($request, true);
    });

    Route::delete('delete', function (Request $request) {
        return lmSetBookmark($request, false);
    });
});

Route::group(['prefix' => 'api/v1/badges'], function () {
    Route::get('list', function () {
        return Response::json(['data' => lmMockBadges()]);
    });
});

Route::group(['prefix' => 'api/v1/users'], function () {
    Route::group(['middleware' => [JwtMiddleware::class]], function () {
        Route::get('my/badges', function (Request $request) {
            $userId = (int) $request->attributes->get('api_user')->id;
            return Response::json(['data' => lmMockUserBadges($userId)]);
        });

        Route::get('my/pictures', function (Request $request) {
            $userId = (int) $request->attributes->get('api_user')->id;
            return Response::json(['data' => lmUserPictures($userId)]);
        });

        Route::get('my/bookmarks', function (Request $request) {
            $userId = (int) $request->attributes->get('api_user')->id;
            return Response::json(lmUserBookmarks($userId));
        });

        Route::post('folders/create', function (Request $request) {
            $validator = Validator::make($request->all(), [
                'name'      => 'required|string|between:2,255',
                'is_public' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
            }

            if (!lmEngagementTableExists('quivi_poi_bookmark_folders')) {
                return Response::json(['error' => 'Bookmark folder storage is not available.'], 500);
            }

            $now = date('Y-m-d H:i:s');
            $id  = Db::table('quivi_poi_bookmark_folders')->insertGetId([
                'user_id'    => (int) $request->attributes->get('api_user')->id,
                'name'       => $request->input('name'),
                'is_public'  => $request->boolean('is_public', false),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return Response::json([
                'status'    => 'OK',
                'id'        => $id,
                'name'      => $request->input('name'),
                'is_public' => $request->boolean('is_public', false),
            ], 201);
        });

        Route::match(['put', 'patch'], 'folders/{id}/update', function (Request $request, $id) {
            $validator = Validator::make($request->all(), [
                'name'      => 'sometimes|required|string|between:2,255',
                'is_public' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
            }

            $userId = (int) $request->attributes->get('api_user')->id;

            if (lmEngagementTableExists('quivi_poi_bookmark_folders')) {
                $fields = ['updated_at' => date('Y-m-d H:i:s')];
                if ($request->has('name'))      $fields['name']      = $request->input('name');
                if ($request->has('is_public')) $fields['is_public'] = $request->boolean('is_public');

                $updated = Db::table('quivi_poi_bookmark_folders')
                    ->where('id', (int) $id)
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->update($fields);

                if ($updated) {
                    $row = Db::table('quivi_poi_bookmark_folders')->where('id', (int) $id)->first();
                    return Response::json([
                        'status'    => 'OK',
                        'id'        => (int) $id,
                        'name'      => $row->name,
                        'is_public' => (bool) $row->is_public,
                    ]);
                }
            }

            foreach (lmMockBookmarkFolderDefinitions() as $folder) {
                if ((int) $folder['id'] === (int) $id) {
                    return Response::json(['status' => 'OK', 'id' => (int) $id]);
                }
            }

            return Response::json(['error' => 'Bookmark folder not found.'], 404);
        });

        Route::delete('folders/{id}/delete', function (Request $request, $id) {
            $userId = (int) $request->attributes->get('api_user')->id;

            if (lmEngagementTableExists('quivi_poi_bookmark_folders')) {
                $updated = Db::table('quivi_poi_bookmark_folders')
                    ->where('id', (int) $id)
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->update([
                        'deleted_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                if ($updated) {
                    if (lmEngagementTableExists('quivi_poi_bookmarks')) {
                        Db::table('quivi_poi_bookmarks')
                            ->where('folder_id', (int) $id)
                            ->where('user_id', $userId)
                            ->whereNull('deleted_at')
                            ->update([
                                'folder_id'  => null,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                    }

                    return Response::json(['success' => true, 'id' => (int) $id]);
                }
            }

            foreach (lmMockBookmarkFolderDefinitions() as $folder) {
                if ((int) $folder['id'] === (int) $id) {
                    return Response::json(['success' => true, 'id' => (int) $id]);
                }
            }

            return Response::json(['error' => 'Bookmark folder not found.'], 404);
        });

    });

    // GET folders/{id} — public folders accessible without auth
    Route::get('folders/{id}', function (Request $request, $id) {
        if (!lmEngagementTableExists('quivi_poi_bookmark_folders')) {
            return Response::json(['error' => 'Bookmark folder not found.'], 404);
        }

        $folder = Db::table('quivi_poi_bookmark_folders')
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$folder) {
            return Response::json(['error' => 'Bookmark folder not found.'], 404);
        }

        // Resolve calling user (optional auth)
        $callerUserId = null;
        $middleware   = new \Quivi\Profile\Classes\JwtMiddleware();
        $middleware->handle($request, function ($req) use (&$callerUserId) {
            $user = $req->attributes->get('api_user');
            if ($user) {
                $callerUserId = (int) $user->id;
            }
        });

        $isOwner = $callerUserId && $callerUserId === (int) $folder->user_id;

        if (!$folder->is_public && !$isOwner) {
            return Response::json(['error' => 'Forbidden.'], 403);
        }

        $bookmarks = lmUserBookmarks((int) $folder->user_id, (int) $id);
        unset($bookmarks['folders']);

        return Response::json([
            'id'        => (int) $id,
            'name'      => $folder->name,
            'is_public' => (bool) $folder->is_public,
            'bookmarks' => $bookmarks,
        ]);
    })->where('id', '[0-9]+');

    Route::get('{id}/profile', function ($id) {
        return Response::json([
            'id'            => (int) $id,
            'username'      => lmMockUser($id)['username'],
            'avatar'        => lmMockUser($id)['avatar'],
            'num_followers' => 128,
            'num_followeds' => 86,
            'num_comments'  => 34,
            'num_pics'      => 12,
            'num_bookmarks' => 19,
            'num_badges'    => count(lmMockUserBadges($id)),
            'badges'        => lmMockUserBadges($id),
            'followers'     => [lmMockUser(2), lmMockUser(3), lmMockUser(4)],
            'followeds'     => [lmMockUser(1), lmMockUser(3)],
            'ranking'       => lmUserRanking((int) $id),
        ]);
    });

    Route::get('{id}/pictures', function ($id) {
        return Response::json([
            'data' => lmUserPictures((int) $id),
        ]);
    });
});

// ---------------------------------------------------------------------------
// Itineraries
// ---------------------------------------------------------------------------

function lmItineraryTableExists()
{
    static $exists = null;
    if ($exists === null) {
        $exists = Schema::hasTable('quivi_poi_itineraries');
    }
    return $exists;
}

function lmItineraryStopResponse($stop, $poiRow = null)
{
    return [
        'id'         => (int) $stop->id,
        'sort_order' => (int) $stop->sort_order,
        'note'       => $stop->note,
        'poi'        => $poiRow ? lmPoiSummary($poiRow) : ['id' => (int) $stop->poi_id],
    ];
}

function lmItineraryResponse($row, bool $withStops = false)
{
    $data = [
        'id'          => (int) $row->id,
        'title'       => $row->title,
        'description' => $row->description,
        'stops_count' => (int) Db::table('quivi_poi_itinerary_stops')->where('itinerary_id', $row->id)->count(),
        'created_at'  => $row->created_at,
        'updated_at'  => $row->updated_at,
    ];

    if ($withStops) {
        $stops  = Db::table('quivi_poi_itinerary_stops')
            ->where('itinerary_id', $row->id)
            ->orderBy('sort_order')
            ->get();
        $poiIds = $stops->pluck('poi_id')->unique()->values()->toArray();
        $poiRows = Db::table('quivi_poi_pois')
            ->select(lmPoiSelectedColumns())
            ->whereIn('id', $poiIds)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id');

        $data['stops'] = $stops->map(function ($stop) use ($poiRows) {
            return lmItineraryStopResponse($stop, $poiRows->get($stop->poi_id));
        })->values()->toArray();
    }

    return $data;
}

Route::group(['prefix' => 'api/v1/itineraries'], function () {

    // GET /itineraries
    Route::get('', function (Request $request) {
        if (!lmItineraryTableExists()) {
            return Response::json(['data' => []]);
        }
        $rows = Db::table('quivi_poi_itineraries')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        return Response::json(['data' => $rows->map(fn($r) => lmItineraryResponse($r))->values()]);
    });

    // POST /itineraries/create
    Route::post('create', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|between:1,255',
            'description' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        if (!lmItineraryTableExists()) {
            return Response::json(['error' => 'Itinerary storage is not available.'], 500);
        }

        $now = date('Y-m-d H:i:s');
        $id  = Db::table('quivi_poi_itineraries')->insertGetId([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $row = Db::table('quivi_poi_itineraries')->where('id', $id)->first();
        return Response::json(lmItineraryResponse($row, true), 201);
    });

    // GET /itineraries/{id}
    Route::get('{id}', function (Request $request, $id) {
        if (!lmItineraryTableExists()) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }
        $row = Db::table('quivi_poi_itineraries')->where('id', (int) $id)->whereNull('deleted_at')->first();

        if (!$row) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        return Response::json(lmItineraryResponse($row, true));
    });

    // PATCH /itineraries/{id}/update
    Route::match(['put', 'patch'], '{id}/update', function (Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|required|string|between:1,255',
            'description' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        if (!lmItineraryTableExists()) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        $row = Db::table('quivi_poi_itineraries')->where('id', (int) $id)->whereNull('deleted_at')->first();
        if (!$row) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        $fields = ['updated_at' => date('Y-m-d H:i:s')];
        if ($request->has('title'))       $fields['title']       = $request->input('title');
        if ($request->has('description')) $fields['description'] = $request->input('description');

        Db::table('quivi_poi_itineraries')->where('id', (int) $id)->update($fields);
        $row = Db::table('quivi_poi_itineraries')->where('id', (int) $id)->first();
        return Response::json(lmItineraryResponse($row, true));
    });

    // DELETE /itineraries/{id}/delete
    Route::delete('{id}/delete', function (Request $request, $id) {
        if (!lmItineraryTableExists()) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        $row = Db::table('quivi_poi_itineraries')->where('id', (int) $id)->whereNull('deleted_at')->first();
        if (!$row) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        Db::table('quivi_poi_itineraries')->where('id', (int) $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        return Response::json(['success' => true, 'id' => (int) $id]);
    });

    // POST /itineraries/{id}/stops/add
    Route::post('{id}/stops/add', function (Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'poi_id'     => 'required|integer|min:1',
            'sort_order' => 'nullable|integer|min:1',
            'note'       => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        if (!lmItineraryTableExists()) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        $row = Db::table('quivi_poi_itineraries')->where('id', (int) $id)->whereNull('deleted_at')->first();
        if (!$row) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        $sortOrder = $request->has('sort_order')
            ? (int) $request->input('sort_order')
            : (int) Db::table('quivi_poi_itinerary_stops')->where('itinerary_id', (int) $id)->max('sort_order') + 1;

        $now    = date('Y-m-d H:i:s');
        $stopId = Db::table('quivi_poi_itinerary_stops')->insertGetId([
            'itinerary_id' => (int) $id,
            'poi_id'       => (int) $request->input('poi_id'),
            'sort_order'   => max(1, $sortOrder),
            'note'         => $request->input('note'),
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        $stop   = Db::table('quivi_poi_itinerary_stops')->where('id', $stopId)->first();
        $poiRow = Db::table('quivi_poi_pois')->select(lmPoiSelectedColumns())->where('id', $stop->poi_id)->first();
        return Response::json(lmItineraryStopResponse($stop, $poiRow), 201);
    });

    // PATCH /itineraries/{id}/stops/{stopId}/update
    Route::match(['put', 'patch'], '{id}/stops/{stopId}/update', function (Request $request, $id, $stopId) {
        $validator = Validator::make($request->all(), [
            'sort_order' => 'nullable|integer|min:1',
            'note'       => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        if (!lmItineraryTableExists()) {
            return Response::json(['error' => 'Stop not found.'], 404);
        }

        $stop = Db::table('quivi_poi_itinerary_stops')
            ->where('id', (int) $stopId)
            ->where('itinerary_id', (int) $id)
            ->first();

        if (!$stop) {
            return Response::json(['error' => 'Stop not found.'], 404);
        }

        $fields = ['updated_at' => date('Y-m-d H:i:s')];
        if ($request->has('sort_order')) $fields['sort_order'] = max(1, (int) $request->input('sort_order'));
        if ($request->has('note'))       $fields['note']       = $request->input('note');

        Db::table('quivi_poi_itinerary_stops')->where('id', (int) $stopId)->update($fields);
        $stop   = Db::table('quivi_poi_itinerary_stops')->where('id', (int) $stopId)->first();
        $poiRow = Db::table('quivi_poi_pois')->select(lmPoiSelectedColumns())->where('id', $stop->poi_id)->first();
        return Response::json(lmItineraryStopResponse($stop, $poiRow));
    });

    // DELETE /itineraries/{id}/stops/{stopId}/remove
    Route::delete('{id}/stops/{stopId}/remove', function (Request $request, $id, $stopId) {
        if (!lmItineraryTableExists()) {
            return Response::json(['error' => 'Stop not found.'], 404);
        }

        $deleted = Db::table('quivi_poi_itinerary_stops')
            ->where('id', (int) $stopId)
            ->where('itinerary_id', (int) $id)
            ->delete();

        if (!$deleted) {
            return Response::json(['error' => 'Stop not found.'], 404);
        }

        return Response::json(['success' => true, 'id' => (int) $stopId]);
    });

    // POST /itineraries/{id}/stops/reorder — body: { "stops": [{"id":1,"sort_order":1},{"id":2,"sort_order":2}] }
    Route::post('{id}/stops/reorder', function (Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'stops'                => 'required|array|min:1',
            'stops.*.id'           => 'required|integer',
            'stops.*.sort_order'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        if (!lmItineraryTableExists()) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        $row = Db::table('quivi_poi_itineraries')->where('id', (int) $id)->whereNull('deleted_at')->first();
        if (!$row) {
            return Response::json(['error' => 'Itinerary not found.'], 404);
        }

        foreach ($request->input('stops') as $item) {
            Db::table('quivi_poi_itinerary_stops')
                ->where('id', (int) $item['id'])
                ->where('itinerary_id', (int) $id)
                ->update(['sort_order' => (int) $item['sort_order'], 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $updatedRow = Db::table('quivi_poi_itineraries')->where('id', (int) $id)->first();
        return Response::json(lmItineraryResponse($updatedRow, true));
    });
});

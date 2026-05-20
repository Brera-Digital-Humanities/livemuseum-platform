<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

function lmMockComments()
{
    return [
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
    ];
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
            'likes_num' => 126,
            'bookmarks_num' => 31,
            'comments_num' => 8,
        ],
    ];

    return array_map(function ($picture) {
        $picture['likes'] = ['users' => [lmMockUser(1), lmMockUser(2), lmMockUser(4)]];
        $picture['comments'] = ['comments' => lmMockComments()];
        $picture['bookmarks'] = ['users' => [lmMockUser(2), lmMockUser(3)]];

        return $picture;
    }, $pictures);
}

function lmMockPois()
{
    $pois = [
        [
            'id' => 101,
            'id_opendata' => 'LM.101',
            'code' => 'duomo-milano',
            'slug' => 'duomo-milano',
            'type' => 'Monumento',
            'title' => 'Duomo di Milano',
            'fulladdress' => 'Piazza del Duomo - Milano',
            'address' => 'Piazza del Duomo',
            'region' => 'Lombardia',
            'province' => 'Milano',
            'city' => 'Milano',
            'zipcode' => '20122',
            'lat' => 45.464211,
            'lng' => 9.191383,
            'distance' => 120,
            'category' => ['id' => 1, 'name_en' => 'Monument', 'name_it' => 'Monumento'],
            'descr' => 'Cattedrale gotica nel centro di Milano, simbolo della citta.',
            'image_url' => 'https://picsum.photos/seed/lm-duomo/1200/900',
            'services' => ['Bookshop', 'Audioguide', 'Visite guidate', 'Accesso terrazze'],
            'num_pictures' => 328,
            'num_likes' => 1420,
            'num_bookmarks' => 284,
            'num_comments' => 96,
            'days_open' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            'hours_open' => '09:00-19:00',
            'tickets_info' => 'Biglietti da 8 EUR, terrazze e museo con tariffe dedicate.',
            'booking_info' => 'Prenotazione consigliata online per terrazze e fasce weekend.',
        ],
        [
            'id' => 102,
            'id_opendata' => 'LM.102',
            'code' => 'pinacoteca-brera',
            'slug' => 'pinacoteca-brera',
            'type' => 'Museo, galleria non a scopo di lucro e/o raccolta',
            'title' => 'Pinacoteca di Brera',
            'fulladdress' => 'Via Brera, 28 - Milano',
            'address' => 'Via Brera',
            'region' => 'Lombardia',
            'province' => 'Milano',
            'city' => 'Milano',
            'zipcode' => '20121',
            'lat' => 45.472026,
            'lng' => 9.188545,
            'distance' => 760,
            'category' => ['id' => 2, 'name_en' => 'Museum', 'name_it' => 'Museo'],
            'descr' => 'Museo d arte con capolavori italiani dal Rinascimento al Novecento.',
            'image_url' => 'https://picsum.photos/seed/lm-brera/1200/900',
            'services' => ['Bookshop', 'Guardaroba', 'Guide e cataloghi', 'Visite guidate'],
            'num_pictures' => 211,
            'num_likes' => 980,
            'num_bookmarks' => 207,
            'num_comments' => 53,
            'days_open' => ['tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            'hours_open' => '08:30-19:15',
            'tickets_info' => 'Ingresso intero 15 EUR, riduzioni disponibili.',
            'booking_info' => 'Prenotazione online raccomandata.',
        ],
        [
            'id' => 103,
            'id_opendata' => 'LM.103',
            'code' => 'castello-sforzesco',
            'slug' => 'castello-sforzesco',
            'type' => 'Monumento',
            'title' => 'Castello Sforzesco',
            'fulladdress' => 'Piazza Castello - Milano',
            'address' => 'Piazza Castello',
            'region' => 'Lombardia',
            'province' => 'Milano',
            'city' => 'Milano',
            'zipcode' => '20121',
            'lat' => 45.470476,
            'lng' => 9.179789,
            'distance' => 980,
            'category' => ['id' => 1, 'name_en' => 'Monument', 'name_it' => 'Monumento'],
            'descr' => 'Fortezza rinascimentale con musei civici e cortili monumentali.',
            'image_url' => 'https://picsum.photos/seed/lm-castello/1200/900',
            'services' => ['Bookshop', 'Didascalie', 'Spazi espositivi', 'Visite guidate'],
            'num_pictures' => 287,
            'num_likes' => 1134,
            'num_bookmarks' => 249,
            'num_comments' => 78,
            'days_open' => ['tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            'hours_open' => '07:00-19:30',
            'tickets_info' => 'Cortili gratuiti, musei civici con biglietto dedicato.',
            'booking_info' => 'Prenotazione suggerita per mostre temporanee.',
        ],
        [
            'id' => 104,
            'id_opendata' => 'LM.104',
            'code' => 'museo-novecento',
            'slug' => 'museo-novecento',
            'type' => 'Museo, galleria non a scopo di lucro e/o raccolta',
            'title' => 'Museo del Novecento',
            'fulladdress' => 'Piazza del Duomo, 8 - Milano',
            'address' => 'Piazza del Duomo',
            'region' => 'Lombardia',
            'province' => 'Milano',
            'city' => 'Milano',
            'zipcode' => '20123',
            'lat' => 45.463620,
            'lng' => 9.190248,
            'distance' => 180,
            'category' => ['id' => 2, 'name_en' => 'Museum', 'name_it' => 'Museo'],
            'descr' => 'Collezione dedicata all arte italiana del XX secolo in Piazza Duomo.',
            'image_url' => 'https://picsum.photos/seed/lm-novecento/1200/900',
            'services' => ['Bookshop', 'Guardaroba', 'Sala per la didattica', 'Visite guidate'],
            'num_pictures' => 156,
            'num_likes' => 744,
            'num_bookmarks' => 139,
            'num_comments' => 41,
            'days_open' => ['tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            'hours_open' => '10:00-19:30',
            'tickets_info' => 'Ingresso intero 5 EUR, ridotto 3 EUR.',
            'booking_info' => 'Prenotazione online disponibile.',
        ],
        [
            'id' => 105,
            'id_opendata' => 'LM.105',
            'code' => 'cenacolo-vinciano',
            'slug' => 'cenacolo-vinciano',
            'type' => 'Museo, galleria non a scopo di lucro e/o raccolta',
            'title' => 'Cenacolo Vinciano',
            'fulladdress' => 'Piazza di Santa Maria delle Grazie, 2 - Milano',
            'address' => 'Piazza di Santa Maria delle Grazie',
            'region' => 'Lombardia',
            'province' => 'Milano',
            'city' => 'Milano',
            'zipcode' => '20123',
            'lat' => 45.465999,
            'lng' => 9.171367,
            'distance' => 1680,
            'category' => ['id' => 3, 'name_en' => 'Heritage Site', 'name_it' => 'Sito storico'],
            'descr' => 'Museo che conserva l Ultima Cena di Leonardo da Vinci.',
            'image_url' => 'https://picsum.photos/seed/lm-cenacolo/1200/900',
            'services' => ['Didascalie', 'Guide e cataloghi', 'Visite guidate'],
            'num_pictures' => 94,
            'num_likes' => 1328,
            'num_bookmarks' => 321,
            'num_comments' => 67,
            'days_open' => ['tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            'hours_open' => '08:15-19:00',
            'tickets_info' => 'Biglietto intero 15 EUR piu prevendita.',
            'booking_info' => 'Prenotazione obbligatoria con slot orario.',
        ],
    ];

    $basePois = $pois;

    for ($index = count($pois) + 1; $index <= 100; $index++) {
        $source = $basePois[($index - 1) % count($basePois)];
        $id = 100 + $index;
        $suffix = str_pad((string) $index, 3, '0', STR_PAD_LEFT);

        $source['id'] = $id;
        $source['id_opendata'] = 'LM.' . $id;
        $source['code'] = $source['code'] . '-mock-' . $suffix;
        $source['slug'] = $source['slug'] . '-mock-' . $suffix;
        $source['title'] = $source['title'] . ' Mock ' . $suffix;
        $source['fulladdress'] = 'Via Mock ' . $index . ' - Milano';
        $source['address'] = 'Via Mock ' . $index;
        $source['zipcode'] = '20' . str_pad((string) (100 + $index), 3, '0', STR_PAD_LEFT);
        $source['lat'] = round($source['lat'] + (($index % 20) - 10) * 0.001, 6);
        $source['lng'] = round($source['lng'] + (($index % 16) - 8) * 0.001, 6);
        $source['distance'] = 200 + ($index * 37);
        $source['descr'] = 'POI mock numero ' . $suffix . ' con dati correlati fittizi per sviluppo e test.';
        $source['image_url'] = 'https://picsum.photos/seed/lm-poi-' . $id . '/1200/900';
        $source['num_pictures'] = 20 + $index;
        $source['num_likes'] = 100 + ($index * 9);
        $source['num_bookmarks'] = 10 + ($index * 3);
        $source['num_comments'] = 5 + ($index * 2);
        $source['tickets_info'] = 'Informazioni biglietti mock per POI ' . $suffix . '.';
        $source['booking_info'] = 'Prenotazione mock disponibile per POI ' . $suffix . '.';

        $pois[] = $source;
    }

    $pictures = lmMockPictures();

    return array_map(function ($poi) use ($pictures) {
        $poiPictures = array_values(array_filter($pictures, function ($picture) use ($poi) {
            return (int) $picture['poi_id'] === (int) $poi['id'];
        }));

        if (empty($poiPictures)) {
            $poiPictures = [
                [
                    'id' => ((int) $poi['id'] * 10) + 1,
                    'poi_id' => (int) $poi['id'],
                    'user' => lmMockUser(((int) $poi['id'] % 4) + 1),
                    'picture' => 'https://picsum.photos/seed/lm-poi-' . $poi['id'] . '-picture-1/1200/900',
                    'created_at' => '2026-05-' . str_pad((string) (((int) $poi['id'] % 20) + 1), 2, '0', STR_PAD_LEFT) . 'T10:00:00+02:00',
                    'likes_num' => 20 + ((int) $poi['id'] % 80),
                    'bookmarks_num' => 5 + ((int) $poi['id'] % 30),
                    'comments_num' => 2 + ((int) $poi['id'] % 12),
                    'likes' => ['users' => [lmMockUser(1), lmMockUser(2)]],
                    'comments' => ['comments' => lmMockComments()],
                    'bookmarks' => ['users' => [lmMockUser(3)]],
                ],
                [
                    'id' => ((int) $poi['id'] * 10) + 2,
                    'poi_id' => (int) $poi['id'],
                    'user' => lmMockUser((((int) $poi['id'] + 1) % 4) + 1),
                    'picture' => 'https://picsum.photos/seed/lm-poi-' . $poi['id'] . '-picture-2/1200/900',
                    'created_at' => '2026-05-' . str_pad((string) (((int) $poi['id'] % 20) + 1), 2, '0', STR_PAD_LEFT) . 'T16:30:00+02:00',
                    'likes_num' => 12 + ((int) $poi['id'] % 60),
                    'bookmarks_num' => 3 + ((int) $poi['id'] % 20),
                    'comments_num' => 1 + ((int) $poi['id'] % 8),
                    'likes' => ['users' => [lmMockUser(2), lmMockUser(4)]],
                    'comments' => ['comments' => lmMockComments()],
                    'bookmarks' => ['users' => [lmMockUser(1), lmMockUser(4)]],
                ],
            ];
        }

        $poi['last_picture'] = $poiPictures[0] ?? null;
        $poi['pictures'] = $poiPictures;
        $poi['likes'] = ['users' => [lmMockUser(1), lmMockUser(2), lmMockUser(4)]];
        $poi['comments'] = ['comments' => lmMockComments()];
        $poi['bookmarks'] = ['users' => [lmMockUser(2), lmMockUser(3)]];

        return $poi;
    }, $pois);
}

function lmMockPoi($identifier)
{
    foreach (lmMockPois() as $poi) {
        if ((string) $poi['id'] === (string) $identifier || $poi['slug'] === (string) $identifier) {
            return $poi;
        }
    }

    return null;
}

function lmMockSearchPois($query)
{
    $query = trim((string) $query);

    if ($query === '') {
        return [];
    }

    return array_values(array_filter(lmMockPois(), function ($poi) use ($query) {
        return stripos($poi['title'], $query) !== false;
    }));
}

function lmMockPicture($id)
{
    foreach (lmMockPictures() as $picture) {
        if ((int) $picture['id'] === (int) $id) {
            $picture['poi'] = lmMockPoi($picture['poi_id']);
            unset($picture['poi_id']);

            return $picture;
        }
    }

    return null;
}

function lmMockCurrentUserId(Request $request)
{
    $user = $request->attributes->get('api_user');

    return $user ? (int) $user->id : (int) $request->input('user_id', 1);
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

        $pois = lmMockPois();

        return Response::json([
            'data' => $pois,
            'meta' => [
                'total' => count($pois),
                'paginated' => false,
            ],
        ]);
    });

    Route::get('list', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required_without:lon|numeric',
            'lon' => 'required_without:lng|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        return Response::json(['data' => lmMockPois()]);
    });

    Route::get('categories', function () {
        return Response::json([
            'data' => [
                ['id' => 1, 'name_en' => 'Monument', 'name_it' => 'Monumento'],
                ['id' => 2, 'name_en' => 'Museum', 'name_it' => 'Museo'],
                ['id' => 3, 'name_en' => 'Heritage Site', 'name_it' => 'Sito storico'],
                ['id' => 4, 'name_en' => 'Gallery', 'name_it' => 'Galleria'],
            ],
        ]);
    });

    Route::get('search', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        return Response::json(['data' => lmMockSearchPois($request->input('q'))]);
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
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
        }

        return Response::json(['status' => 'OK', 'id' => 999], 201);
    });

    Route::get('{identifier}/view', function ($identifier) {
        $poi = lmMockPoi($identifier);

        if (!$poi) {
            return Response::json(['error' => 'POI not found.'], 404);
        }

        return Response::json($poi);
    });
});

Route::group(['prefix' => 'api/v1/pictures'], function () {
    Route::get('list', function () {
        $pictures = array_map(function ($picture) {
            $picture['poi'] = lmMockPoi($picture['poi_id']);
            unset($picture['poi_id'], $picture['likes'], $picture['comments'], $picture['bookmarks']);

            return $picture;
        }, lmMockPictures());

        return Response::json(['data' => $pictures]);
    });

    Route::get('{id}/view', function ($id) {
        $picture = lmMockPicture($id);

        if (!$picture) {
            return Response::json(['error' => 'Picture not found.'], 404);
        }

        return Response::json($picture);
    });

    Route::post('create', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'poi_id' => 'required|integer',
            'picture' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
        }

        return Response::json([
            'status' => 'OK',
            'id' => 900,
            'poi_id' => (int) $request->input('poi_id'),
            'user' => lmMockUser(lmMockCurrentUserId($request)),
        ], 201);
    });

    Route::delete('{id}/delete', function (Request $request, $id) {
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
    Route::post('{id}/create', function (Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'comment_text' => 'required|string|between:1,1000',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        return Response::json([
            'id' => 900,
            'parent_id' => (int) $id,
            'user' => lmMockUser(lmMockCurrentUserId($request)),
            'comment_text' => $request->input('comment_text'),
            'comment_date' => date('c'),
            'likes_num' => 0,
            'likes' => ['users' => []],
            'comments_num' => 0,
            'comments' => [],
        ], 201);
    });

    Route::delete('{id}/delete', function (Request $request, $id) {
        $ownerUserId = (int) $request->input('owner_user_id', 1);

        if ($ownerUserId !== lmMockCurrentUserId($request)) {
            return Response::json(['error' => 'Forbidden.'], 403);
        }

        return Response::json(['success' => true, 'id' => (int) $id]);
    });

    Route::match(['put', 'patch'], '{id}/update', function (Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'comment_text' => 'required|string|between:1,1000',
            'owner_user_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $ownerUserId = (int) $request->input('owner_user_id', 1);

        if ($ownerUserId !== lmMockCurrentUserId($request)) {
            return Response::json(['error' => 'Forbidden.'], 403);
        }

        return Response::json([
            'id' => (int) $id,
            'user' => lmMockUser($ownerUserId),
            'comment_text' => $request->input('comment_text'),
            'comment_date' => date('c'),
            'likes_num' => 0,
            'likes' => ['users' => []],
            'comments_num' => 0,
            'comments' => [],
        ]);
    });
});

Route::group(['prefix' => 'api/v1/badges'], function () {
    Route::get('list', function () {
        return Response::json(['data' => lmMockBadges()]);
    });
});

Route::group(['prefix' => 'api/v1/users'], function () {
    Route::get('my/badges', function (Request $request) {
        return Response::json([
            'data' => lmMockUserBadges(lmMockCurrentUserId($request)),
        ]);
    });

    Route::get('my/pictures', function () {
        return Response::json([
            'data' => array_values(array_filter(lmMockPictures(), function ($picture) {
                return (int) $picture['user']['id'] === 1;
            })),
        ]);
    });

    Route::get('my/bookmarks', function () {
        return Response::json([
            'pois' => [lmMockPoi(101), lmMockPoi(105)],
            'pictures' => [lmMockPicture(502)],
            'folders' => [
                ['id' => 701, 'name' => 'Weekend a Milano', 'bookmarks_num' => 3],
                ['id' => 702, 'name' => 'Arte moderna', 'bookmarks_num' => 1],
            ],
        ]);
    });

    Route::post('folders/create', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,255',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
        }

        return Response::json(['status' => 'OK', 'id' => 703, 'name' => $request->input('name')], 201);
    });

    Route::match(['put', 'patch'], 'folders/{id}/update', function (Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,255',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'KO', 'errors' => $validator->errors()], 422);
        }

        return Response::json(['status' => 'OK', 'id' => (int) $id, 'name' => $request->input('name')]);
    });

    Route::delete('folders/{id}/delete', function ($id) {
        return Response::json(['success' => true, 'id' => (int) $id]);
    });

    Route::get('folders/{id}', function ($id) {
        return Response::json([
            'id' => (int) $id,
            'name' => 'Weekend a Milano',
            'bookmarks' => [
                'pois' => [lmMockPoi(101), lmMockPoi(103)],
                'pictures' => [lmMockPicture(501)],
            ],
        ]);
    });

    Route::get('{id}/profile', function ($id) {
        return Response::json([
            'id' => (int) $id,
            'username' => lmMockUser($id)['username'],
            'avatar' => lmMockUser($id)['avatar'],
            'num_followers' => 128,
            'num_followeds' => 86,
            'num_comments' => 34,
            'num_pics' => 12,
            'num_bookmarks' => 19,
            'num_badges' => count(lmMockUserBadges($id)),
            'badges' => lmMockUserBadges($id),
            'followers' => [lmMockUser(2), lmMockUser(3), lmMockUser(4)],
            'followeds' => [lmMockUser(1), lmMockUser(3)],
        ]);
    });

    Route::get('{id}/pictures', function ($id) {
        return Response::json([
            'data' => array_values(array_filter(lmMockPictures(), function ($picture) use ($id) {
                return (int) $picture['user']['id'] === (int) $id;
            })),
        ]);
    });
});

<?php namespace Quivi\Poi\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Db;

class BookmarkFolders extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Quivi.Poi', 'poi', 'bookmarkfolders');
    }

    public function create()
    {
        $this->asExtension('FormController')->create();
    }

    public function create_onSave()
    {
        return $this->asExtension('FormController')->create_onSave();
    }

    public function update($recordId = null)
    {
        $this->asExtension('FormController')->update($recordId);
    }

    public function update_onSave($recordId = null)
    {
        return $this->asExtension('FormController')->update_onSave($recordId);
    }

    public function update_onDelete($recordId = null)
    {
        return $this->asExtension('FormController')->update_onDelete($recordId);
    }

    public function onAddBookmarkItem()
    {
        $folderId   = (int) post('folder_id');
        $targetType = trim((string) post('target_type'));
        $targetId   = (int) post('target_id');

        $allowed = ['poi', 'picture', 'itinerary'];
        if (!in_array($targetType, $allowed) || !$targetId || !$folderId) {
            Flash::error('Dati non validi.');
            return;
        }

        $exists = Db::table('quivi_poi_bookmarks')
            ->where('folder_id', $folderId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            $folder = Db::table('quivi_poi_bookmark_folders')->where('id', $folderId)->first();
            $now = date('Y-m-d H:i:s');
            Db::table('quivi_poi_bookmarks')->insert([
                'user_id'     => $folder->user_id,
                'folder_id'   => $folderId,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            Flash::success('Elemento aggiunto.');
        } else {
            Flash::warning('Elemento già presente.');
        }
    }

    public function onRemoveBookmarkItem()
    {
        $bookmarkId = (int) post('bookmark_id');
        $folderId   = (int) post('folder_id');

        Db::table('quivi_poi_bookmarks')
            ->where('id', $bookmarkId)
            ->where('folder_id', $folderId)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        Flash::success('Elemento rimosso.');
        return \Redirect::refresh();
    }
}

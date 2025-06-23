<?php

namespace Sanil\FileManager\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Log;
use Sanil\FileManager\Models\Upload;

trait Upload
{
    /**
     * Define polymorphic relationship.
     */
    public function uploads(): MorphToMany
    {
        return $this->morphToMany( Upload::class, 'uploadable', 'uploadables' );
    }

    /**
     * Attach an existing upload to the model.
     */
    public function attachUpload( Upload $upload ): bool
    {
        try {
            $this->uploads()->syncWithoutDetaching( [ $upload->id ] );
            return true;
        }
        catch ( \Exception $e ) {
            Log::error( 'attachUpload error', [
                'model'     => get_class( $this ),
                'id'        => $this->id,
                'upload_id' => $upload->id,
                'message'   => $e->getMessage(),
            ] );
            return false;
        }
    }

    /**
     * Detach one or all uploads from the model and optionally delete them.
     */
    public function detachUploads( Upload $upload = null, bool $deleteFiles = false ): bool
    {
        try {
            $uploadsToDetach = $upload ? collect( [ $upload ] ) : $this->uploads;

            foreach ( $uploadsToDetach as $item ) {
                $this->uploads()->detach( $item->id );

                if ( $deleteFiles && $item->exists ) {
                    $item->delete();
                }
            }

            return true;
        }
        catch ( \Exception $e ) {
            Log::error( 'detachUploads error', [
                'model'   => get_class( $this ),
                'id'      => $this->id,
                'message' => $e->getMessage(),
            ] );
            return false;
        }
    }
}

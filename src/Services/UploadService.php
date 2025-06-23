<?php

namespace Sanil\FileManager\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sanil\FileManager\Models\Upload;

class UploadService
{
    /**
     * Upload a file and save metadata in the uploads table.
     *
     * @param string|null $collection
     * @param string      $disk_name
     * @param array|null  $tags
     *
     * @return Upload|false
     */
    public function upload(
        $file,
        ?string $collection = 'default',
        string $disk_name = 'public',
        ?array $tags = null
    ): Upload|false {
        try {
            $uuid = str_replace( '-', '', Str::uuid()->toString() );
            $upload_dir_path = "uploads/{$uuid}";

            $uploadData = $this->handleFileUpload( $file, $upload_dir_path, $disk_name );

            if ( $uploadData === false ) {
                throw new \Exception( 'File upload failed' );
            }

            return Upload::create( [
                'upload_path' => $uploadData[ 'upload_path' ],
                'mime_type'   => $uploadData[ 'mime_type' ],
                'ext'         => $uploadData[ 'extension' ],
                'disk'        => $uploadData[ 'disk' ],
                'size'        => round( $uploadData[ 'size' ], 2 ),
                'collection'  => $collection,
                'tags'        => $tags ? implode( ',', $tags ) : null,
            ] );
        }
        catch ( \Exception $e ) {
            Log::error( 'File upload error: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Handle the physical file storage and image resizing.
     *
     * @param string       $upload_dir_path
     * @param string       $disk_name
     *
     * @return array|false
     */
    protected function handleFileUpload( $file, string $upload_dir_path, string $disk_name ): array|false
    {
        $extension = $file->getClientOriginalExtension();
        $filename = "full.{$extension}";
        $folder_name = $upload_dir_path;
        $relative_path = "{$folder_name}/{$filename}";

        try {
            Storage::disk( $disk_name )->makeDirectory( $folder_name );
            Storage::disk( $disk_name )->putFileAs( $folder_name, $file, $filename );

            $full_path = Storage::disk( $disk_name )->path( $relative_path );
            $visibility = config( "filesystems.disks.{$disk_name}.visibility", 'private' );
            Storage::disk( $disk_name )->setVisibility( $relative_path, $visibility );

            $mime = $file->getClientMimeType();
            $filesize = $file->getSize();

            if ( Str::startsWith( $mime, 'image/' ) ) {
                $image_sizes = config( 'image.size', [] );

                foreach ( $image_sizes as $size ) {
                    [ $width, $height ] = $size;
                    $resized_name = "{$width}x{$height}.{$extension}";
                    $resized_path = "{$folder_name}/{$resized_name}";

                    \Spatie\Image\Image::load( $full_path )
                        ->width( $width )
                        ->height( $height )
                        ->save( Storage::disk( $disk_name )->path( $resized_path ) );

                    Storage::disk( $disk_name )->setVisibility( $resized_path, $visibility );
                }
            }

            return [
                'upload_path' => $folder_name,
                'extension'   => $extension,
                'size'        => $filesize / 1024,
                'mime_type'   => $mime,
                'disk'        => $disk_name,
            ];
        }
        catch ( \Exception $e ) {
            Log::error( 'handleFileUpload error: ' . $e->getMessage() );
            return false;
        }
    }
}

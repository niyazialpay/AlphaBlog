<?php

use Spatie\ImageOptimizer\Optimizers\Avifenc;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Avif;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Image;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Svg;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Video;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Webp;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;
use Spatie\MediaLibrary\Downloaders\DefaultDownloader;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob;
use Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\Blurred;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\FileSizeOptimizedWidthCalculator;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;
use Spatie\MediaLibrary\Support\FileRemover\DefaultFileRemover;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Spatie\MediaLibraryPro\Models\TemporaryUpload;

return [

    'disk_name' => env('MEDIA_DISK', 'public'),

    'max_file_size' => 1024 * 1024 * 100,

    'queue_connection_name' => env('QUEUE_CONNECTION', 'sync'),

    'queue_name' => env('MEDIA_QUEUE', ''),

    'queue_conversions_by_default' => env('QUEUE_CONVERSIONS_BY_DEFAULT', true),

    'queue_conversions_after_database_commit' => env('QUEUE_CONVERSIONS_AFTER_DB_COMMIT', true),

    'media_model' => Media::class,

    'use_default_collection_serialization' => false,

    'temporary_upload_model' => TemporaryUpload::class,

    'enable_temporary_uploads_session_affinity' => true,

    'generate_thumbnails_for_temporary_uploads' => true,

    'file_namer' => DefaultFileNamer::class,

    'path_generator' => DefaultPathGenerator::class,

    'file_remover_class' => DefaultFileRemover::class,

    'custom_path_generators' => [
    ],

    'url_generator' => DefaultUrlGenerator::class,

    'moves_media_on_update' => false,

    'version_urls' => false,

    'image_optimizers' => [
        Jpegoptim::class => [
            '-m85',
            '--force',
            '--strip-all',
            '--all-progressive',
        ],
        Pngquant::class => [
            '--force',
        ],
        Optipng::class => [
            '-i0',
            '-o2',
            '-quiet',
        ],
        Svgo::class => [
            '--disable=cleanupIDs',
        ],
        Gifsicle::class => [
            '-b',
            '-O3',
        ],
        Cwebp::class => [
            '-m 6',
            '-pass 10',
            '-mt',
            '-q 90',
        ],
        Avifenc::class => [
            '-a cq-level=23',
            '-j all',
            '--min 0',
            '--max 63',
            '--minalpha 0',
            '--maxalpha 63',
            '-a end-usage=q',
            '-a tune=ssim',
        ],
    ],

    'image_generators' => [
        Image::class,
        Webp::class,
        Avif::class,
        Pdf::class,
        Svg::class,
        Video::class,
    ],

    'temporary_directory_path' => null,

    'image_driver' => env('IMAGE_DRIVER', 'gd'),

    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    'jobs' => [
        'perform_conversions' => PerformConversionsJob::class,
        'generate_responsive_images' => GenerateResponsiveImagesJob::class,
    ],

    'media_downloader' => DefaultDownloader::class,

    'media_downloader_ssl' => env('MEDIA_DOWNLOADER_SSL', true),

    'remote' => [
        'extra_headers' => [
            'CacheControl' => 'max-age=604800',
        ],
    ],

    'responsive_images' => [
        'width_calculator' => FileSizeOptimizedWidthCalculator::class,

        'use_tiny_placeholders' => true,

        'tiny_placeholder_generator' => Blurred::class,
    ],

    'enable_vapor_uploads' => env('ENABLE_MEDIA_LIBRARY_VAPOR_UPLOADS', false),

    'default_loading_attribute_value' => null,

    'prefix' => env('MEDIA_PREFIX', ''),

    'force_lazy_loading' => env('FORCE_MEDIA_LIBRARY_LAZY_LOADING', true),
];

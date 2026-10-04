<?php

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__).'/resources/views'));
foreach ($files as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }
    $source = file_get_contents($file->getPathname());
    $source = preg_replace('/\s*(@(?:extends|section|endsection|if|elseif|else|endif|foreach|endforeach|forelse|endforelse|empty|php|endphp)\b)/', "\n$1", $source);
    file_put_contents($file->getPathname(), ltrim($source));
}

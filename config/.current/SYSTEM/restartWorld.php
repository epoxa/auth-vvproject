<?php

use YY\Core\Cache;
use YY\Core\Data;
use YY\System\Log;
use YY\System\YY;

YY::Log('system', 'World will be restarted!');

// Завершаем работу с объектной системой
Cache::Flush(false);
Log::finalize();
Data::DetachStorage();

// TODO: Как бы еще все запущенные потоки подождать/убить?

// Удаляем локальный мир
$dh = opendir(DATA_DIR);
while (($file = readdir($dh)) !== false) {
    if (substr($file, 0, 1) !== '.') unlink(DATA_DIR . $file);
}
closedir($dh);
if (function_exists('dba_handlers') && in_array(Data::DBA_HANDLER, dba_handlers())) {
    $dbname = DATA_DIR . 'DATA.db';
    if (file_exists($dbname)) unlink($dbname);
}

YY::redirectUrl(); // В этом случае, похоже, один объект в новом мире становится потерянным (orphan)
// TODO: Сделать, чтобы в этом случае кэш изменений не сбрасывался на диск


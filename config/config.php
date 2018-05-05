<?php

define('CRON_MODE', php_sapi_name() === 'cli');

ini_set('default_charset', 'utf-8');
mb_internal_encoding("UTF-8");

define('ROOT_DIR', realpath(__DIR__ . '/..') . '/');

const LOCK_DIR = ROOT_DIR . 'runtime/lock/';
const CLASSES_DIR =  ROOT_DIR . 'classes/';
const TEMPLATES_DIR = ROOT_DIR . 'templates/';
const CONFIGS_DIR = ROOT_DIR . 'config/';
const DATA_DIR = ROOT_DIR .'runtime/data/';
const LOG_DIR = ROOT_DIR . 'runtime/log/';
const SESSIONS_DIR = ROOT_DIR . 'runtime/sessions/';
const FILES_DIR = ROOT_DIR . 'www/files/';
const TOKENS_DIR = ROOT_DIR . 'runtime/tokens/';

if (!CRON_MODE) {
  define('DOMAIN_NAME', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'localhost'));
  define('PROTOCOL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https://' : 'http://');
  define('ROOT_PATH', ''); // If not empty must have trailing slash but not initial
  define('ROOT_URL', DOMAIN_NAME . '/' . ROOT_PATH);
  define('COOKIE_NAME', 'YY');
  define('INSTALL_COOKIE_NAME', 'XX');
  define('DEFAULT_SESSION_IP_CHECKING', true);
  define('DEFAULT_SESSION_LIFETIME', 3600 * 24 * 3); // Enough through weekends

  ini_set('session.gc_maxlifetime', DEFAULT_SESSION_LIFETIME);
  ini_set('session.cookie_lifetime', DEFAULT_SESSION_LIFETIME);
  ini_set('session.save_path', SESSIONS_DIR);
  ini_set('session.cookie_domain', DOMAIN_NAME);
  ini_set('session.gc_probability', '5');
}

// To be customized

date_default_timezone_set(getenv('YY_TIMEZONE') ?: 'UTC');
define('DEBUG_MODE', true);
define('DEBUG_ALLOWED_IP', CRON_MODE || in_array($_SERVER['REMOTE_ADDR'], $_SERVER['ENV']['YY_TRUSTED_IPS']  ?: ['127.0.0.1'], true));
define('YYID', 'YYID');

// Totally custom

const BOOT_VERSION = 6;
const BOOT_MIN_VERSION = 5;
const OVERLAY_WINDOW_NAME = '_vvsidewindow';
const OVERLAY_WINDOW_PARAMS = 'left=8000,top=0,height=8000,width=360,location=no,toolbar=no,directories=no,status=no,menubar=no';

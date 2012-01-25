<?php
/*
    ==  =\
    = == =
    = ====   SimPlaza.net Mirror Service
    ==  ==   Major Rasputin 2011 - 2012
    ==== =   SIMPL - http://simplaza.net/hax/SIMPL
    = == =
    ==  ==
 */


// SimPlaza.net Common Library at http://common.simplaza.net/php
include_once 'common/c.php';

// Config file (please copy over from index.config.php.example
require_once 'index.config.php';

// ====
// INIT
// ====

if ( !is_dir(DIR_DATA) ) mkdir(DIR_DATA);
if ( !is_dir(DIR_FILES) ) mkdir(DIR_FILES);

$_DB;
$_HASHES;
$_ROW;
$_MESSAGES = array();
$_STATE = array(
    'UPLOAD' => false
);

// ========
// HANDLERS
// ========

// If a file is submitted or something is queried, prepare the databases
if ( !empty($_FILES['filepicker']) || !empty($_GET) ) {
    $_DB = db_load(FILE_DB, 'Could not load master DB');
    $_HASHES = db_load(FILE_HASHES, 'Could not load hashes index');
}

// HANDLER: Uploading file
if ( !empty($_FILES['filepicker']) && is_uploaded_file($_FILES['filepicker']['tmp_name']) ) {
    $_FILE = $_FILES['filepicker'];
    $hash = hash_file('md5',$_FILE['tmp_name']);
    $uuid = uniqid();

    if ( !preg_match(WHITELIST_EXTENSIONS, $_FILE['name']) ) {
        // CHECK: File uploaded is unacceptable...
        messageError('Hey, no cheating! Only ZIP/RAR/7Z files are uploadable.');

    } else if ( in_array($hash, $_HASHES) ) {
        // CHECK: File already exists...
        messageError('This file has already been uploaded!');

    } else {
        $_ROW = array();
        $_ROW['time'] = time();
        $_ROW['filename'] = $_FILE['name'];
        $_ROW['password'] = password($uuid);
        $_ROW['downloads'] = 0;
        $_ROW['uploader'] = $_SERVER['REMOTE_ADDR'];
        $_HASHES[$uuid] = $hash;
        $_DB[$uuid] = $_ROW;

        $_MESSAGES['upload_success'] =
<<<HTML
        <h1 class="center">
            Success! Your file <em>$_ROW[filename]</em> was uploaded.<br />
            Bookmark these links:
        </h1>
            <h3 class="center"><sup>Main link</sup><a href="http://mirror.simplaza.net/$uuid" title="$_ROW[filename]">http://mirror.simplaza.net/$uuid</a></h3>
            <h3 class="center"><sup>Deletion link</sup><a href="http://mirror.simplaza.net/delete/$uuid/$_ROW[password]" title="Delete $_ROW[filename]">http://mirror.simplaza.net/delete/$uuid/$_ROW[password]</a></h3>
HTML;

        move_uploaded_file($_FILE['tmp_name'], DIR_FILES.$uuid);
        $_STATE['UPLOAD'] = true;
        saveAll();
    }
}

// HANDLER: Going to file
if ( isset($_GET['f']) ) {
    $id = $_GET['f'];

    if ( preg_match(BLACKLIST_ADFLY, $_SERVER['HTTP_REFERER']) ) {
        // CHECK: Prevent adfly/adcraft use
        header("Status: 403 Forbidden");

        messageError('This file was linked from an advertisement gateway!',
            'You are not doing the Minecraft community a favour by linking your files through an advertisement gateway to generate income. This is a very abhorrent practise and is not allowed with use of this mirror.'
            .BR.BR.'This has been reported to the administrator. If the uploader of this file is found to be linking to it through an ad-gateway, the file will be deleted off the mirror.');

    } else if ( !key_exists($id, $_DB) ) {
        // CHECK: File doesn't exist...
        header("Status: 404 Not Found");
        messageError('File ID '.$id.' does not exist!');

    } else {
        $_ROW = $_DB[$id];
        $_ROW['downloads']++;
        saveAll();

        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$_ROW['filename'].'"');
        readfile(DIR_FILES.$id);

        exit();
    }
}

if ( isset($_GET['del']) && isset($_GET['password']) ) {
    $id = $_GET['del'];

    if ( !key_exists($id, $_DB) ) {
        messageError('File ID '.$id.' does not exist!');
    } else if ($_DB[$id]['password'] == $_GET['password']) {
        $name = $_DB[$id]['filename'];

        $_MESSAGES['delete_success'] = <<<HTML
        <h1 class="center">Your file <em>$_ROW[filename]</em> was deleted.</h1>
HTML;

        unlink(DIR_FILES.$id);
        unset($name,$_DB[$id], $_HASHES[$id]);
        saveAll();
    } else {
        messageError('Wrong password for file '.$_DB[$id]['filename']);
    }

}

// Save all data when any handling is done
function saveAll() {
    global $_DB, $_HASHES;
    //messageError('Debug', var_export($_DB,true).var_export($_HASHES,true) );
    db_save(FILE_DB, $_DB, 'Could not write main database!');
    db_save(FILE_HASHES, $_HASHES, 'Could not write hash index!');
}

// ==============
// OUTPUT WRITING
// ==============

function messageError($title, $msg = '') {
    global $_MESSAGES;

    array_push($_MESSAGES, '<div class="error"><h1 class="center">'.$title.'</h1>'.$msg.'</div>');
}

// =========
// DATABASES
// =========

function db_load($filename, $error, $secure = true) {
    if ( !is_file($filename) ) return array();

    $file = file_get_contents($filename) or die($error);
    if ($secure) $file = crypter($file, AUTH_DBKEY, false);

    return unserialize($file);
}

function db_save($filename ,$data, $error, $secure = true) {
    $data = serialize($data);
    if ($secure) $data = crypter($data, AUTH_DBKEY);

    $file = fopen($filename, 'w') or die($error);
    fwrite($file, $data);
    fclose($file);

    unset($file);
}

// ========
// SECURITY
// ========

function crypter($data, $key, $crypt = TRUE) {
    $td = mcrypt_module_open('rijndael-256', '', 'ecb', '');

    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    $key = substr($key, 0, mcrypt_enc_get_key_size($td));
    mcrypt_generic_init($td, $key, $iv);

	$data = $crypt ? mcrypt_generic($td, $data) : mdecrypt_generic($td, $data);

    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);

    return $data;
}

function password($uuid) {
    return hash('md5',crypter($uuid, AUTH_PASSWORDSALT));
}

function is_password($uuid, $pass) {
    return ( $pass == password($uuid) ) ? true : false;
}
?>

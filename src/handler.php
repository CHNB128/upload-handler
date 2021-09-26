<?php

/**
 * Checks if a folder exist and return canonicalized absolute pathname (sort version)
 * @param string $folder the path being checked.
 * @return mixed returns the canonicalized absolute pathname on success otherwise FALSE is returned
 */
function is_folder_exist($folder) {
    // Get canonicalized absolute pathname
    $path = realpath($folder);
    // If it exist, check if it's a directory
    return ($path !== false AND is_dir($path)) ? $path : false;
}

function check_upload_allowed($config, $file) {


  return true;
}

//

$upload_path = "upload/";
$config = [
  "image/jpeg" => [ "max_size" => 100 ],
  "image/png"  => [ "max_size" => 100 ],
  "image/gif"  => [ "max_size" => 100 ],
];

if (!is_folder_exist($upload_path)) {
  mkdir($upload_path);
}

try {
  header('Content-Type: application/json');
  http_response_code(200);

  $response = [];
  $file = $_FILES["upfile"];

  if (is_null($file)) {
    throw new RuntimeException('No file sent.');
  }

  $extension = (new finfo(FILEINFO_MIME_TYPE))->file($file);
  $target_file = sprintf('%s/%s.%s', $upload_path, sha1_file($file['tmp_name']), $extension);
  $spec = $config[$extension];

  switch ($file['error']) {
    case UPLOAD_ERR_OK:
      break;
    case UPLOAD_ERR_NO_FILE:
      throw new RuntimeException('No file sent.');
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      throw new RuntimeException('Exceeded filesize limit.');
    default:
      throw new RuntimeException('Unknown errors.');
  }

  if (!isset($file['error']) || is_array($file['error'])) {
    throw new RuntimeException('Invalid parameters.');
  }

  if ($spec == null) {
    throw new RuntimeException('Invalid file format.');
  }

  if ($file['size'] > $spec['max_size']) {
    throw new RuntimeException('Exceeded filesize limit.');
  }

  if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $target_file)) {
    throw new RuntimeException('Failed to move uploaded file.');
  }

  $response = array(
    'url' => '/' . $target_file
  );

  echo json_encode($response);

} catch (RuntimeException $e) {
  http_response_code(500);

  $response = array(
    'error' => $e->getMessage()
  );

  echo json_encode($response);

}

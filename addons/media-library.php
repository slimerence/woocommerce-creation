<?php
function custom_media_size_column($cols)
{
  $cols["file_size"] = "File Size";
  return $cols;
}
add_filter("manage_upload_columns", "custom_media_size_column");

function custom_media_size_value($column_name, $id)
{
  if ($column_name == "file_size") {
    $bytes = filesize(get_attached_file($id));
    echo size_format($bytes);
  }
}
add_action("manage_media_custom_column", "custom_media_size_value", 10, 2);


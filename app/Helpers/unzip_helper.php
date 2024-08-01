<?php


/**
 * Unzip the source_file in the destination dir
 *
 * @param   string      $src_file             The path to the ZIP-file.
 * @param   string      $dest_dir             The path where the zipfile should be unpacked, if false the directory of the zip-file is used
 * @param   boolean     $create_zip_name_dir  Indicates if the files will be unpacked in a directory with the name of the zip-file (true) or not (false) (only if the destination directory is set to false!)
 * @param   boolean     $overwrite            Overwrite existing files (true) or not (false)
 *  
 * @return  boolean     Successful or not
 */
function unzip($src_file, $dest_dir = false, $create_zip_name_dir = true, $overwrite = true)
{
    $zip = new \ZipArchive();
    if ($zip->open($src_file) === true) 
    {
        $splitter = $create_zip_name_dir ? "." : "/";
        if ($dest_dir === false) {
            $dest_dir = substr((string) $src_file, 0, strrpos((string) $src_file, $splitter)) . "/";
        }
        
        // Create the directories to the destination dir if they don't already exist
        create_dirs($dest_dir);

        // For every file in the zip-packet
        for ($i = 0; $i < $zip->numFiles; $i++) 
        {
            $zip_entry = $zip->getNameIndex($i);
            
            // Now we're going to create the directories in the destination directories
            
            // If the file is not in the root dir
            $pos_last_slash = strrpos($zip_entry, "/");
            if ($pos_last_slash !== false)
            {
                // Create the directory where the zip-entry should be saved (with a "/" at the end)
                create_dirs($dest_dir . substr($zip_entry, 0, $pos_last_slash + 1));
            }

            // The name of the file to save on the disk
            $file_name = $dest_dir . $zip_entry;
            
            // Check if the files should be overwritten or not
            if ($overwrite === true || ($overwrite === false && !is_file($file_name)))
            {
                // Get the content of the zip entry
                $fstream = $zip->getFromName($zip_entry);

                if (!is_dir($file_name)) {
                    file_put_contents($file_name, $fstream);
                }
            }
        }
        // Close the zip-file
        $zip->close();
        return true;
    } 
    else 
    {
        return false;
    }
}

/**
 * This function creates recursive directories if they don't already exist
 *
 * @param string $path  The path that should be created
 *  
 * @return void
 */
function create_dirs($path)
{
    if (!is_dir($path))
    {
        $directory_path = "";
        $directories = explode("/", (string) $path);
        array_pop($directories);
        
        foreach ($directories as $directory)
        {
            $directory_path .= $directory . "/";
            if (!is_dir($directory_path))
            {
                mkdir($directory_path, 0777, true);
            }
        }
    }
}

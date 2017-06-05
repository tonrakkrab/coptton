<?php
//namespace App\Http\Controllers\Admin;
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UploadsManager;
use Illuminate\Http\Request;

// Add the following 3 lines at the top, with the use statements
use App\Http\Requests\UploadFileRequest;
use App\Http\Requests\UploadNewFolderRequest;
use Illuminate\Support\Facades\File;
use ZipArchive;


class UploadController extends Controller
{
  protected $manager;

  public function __construct(UploadsManager $manager)
  {
    $this->manager = $manager;
  }

  /**
   * Show page of files / subfolders
   */
  public function index(Request $request)
  {
    $folder = $request->get('folder');
    $data = $this->manager->folderInfo($folder);

    //return view('admin.upload.index', $data);
    return view('exams.practical.upload.index', $data);
  }

  // Add the following 4 methods to the UploadControllerClass
  /**
   * Create a new folder
   */
  public function createFolder(UploadNewFolderRequest $request)
  {
    $new_folder = $request->get('new_folder');    
    $folder = $request->get('folder').'/'.$new_folder;

    $result = $this->manager->createDirectory($folder);

    if ($result === true) {
      /*return redirect()
          ->back()
          ->withSuccess("Folder '$new_folder' created.");*/
      return redirect()->back()->with("message", "Folder '$new_folder' created.");

    }

    $error = $result ? : "An error occurred creating directory.";
    return redirect()
        ->back()
        ->withErrors([$error]);
  }

  /**
   * Delete a file
   */
  public function deleteFile(Request $request)
  {
    $del_file = $request->get('del_file');
    $path = $request->get('folder').'/'.$del_file;

    $result = $this->manager->deleteFile($path);

    if ($result === true) {
      /*return redirect()
          ->back()
          ->withSuccess("File '$del_file' deleted.");*/
      return redirect()->back()->with("message", "File '$del_file' deleted.");
    }

    $error = $result ? : "An error occurred deleting file.";
    return redirect()
        ->back()
        ->withErrors([$error]);
  }

  /**
   * Delete a folder
   */
  public function deleteFolder(Request $request)
  {
    $del_folder = $request->get('del_folder');
    $folder = $request->get('folder').'/'.$del_folder;

    $result = $this->manager->deleteDirectory($folder);

    if ($result === true) {
      /*return redirect()
          ->back()
          ->withSuccess("Folder '$del_folder' deleted.");*/
      return redirect()->back()->with("message", "Folder '$del_folder' deleted.");
    }

    $error = $result ? : "An error occurred deleting directory.";
    return redirect()
        ->back()
        ->withErrors([$error]);
  }

  /**
   * Upload new file
   */
  public function uploadFile(UploadFileRequest $request)
  {
    //var_dump($request);exit(0);

    $file = $_FILES['file'];
    $fileName = $request->get('file_name');
    $fileName = $fileName ?: $file['name'];
    $path = str_finish($request->get('folder'), '/') . $fileName;
    $content = File::get($file['tmp_name']);

    $result = $this->manager->saveFile($path, $content);

    if ($result === true) {
      /*return redirect()
          ->back()
          ->withSuccess("File '$fileName' uploaded.");*/
      return redirect()->back()->with("message", "File '$fileName' uploaded.");
    }

    $error = $result ? : "An error occurred uploading file.";
    return redirect()
        ->back()
        ->withErrors([$error]);
  }

  /**
   * Add zip file
   */
  public function addZipFile(UploadFileRequest $request)
  {  
    $zip = new ZipArchive;
    if ($zip->open('test.zip', ZipArchive::CREATE) === TRUE) {      
      //$zip->addFile('/path/to/index.txt', 'newname.txt');
      $zip->addFile(public_path('/index.php'), 'newname1.php');
      $zip->addFile(public_path('/robots.txt'), 'newname2.txt');
      
      /*
echo "numFiles: " . $zip->numFiles . "\n";
echo "status: " . $zip->status  . "\n";
echo "statusSys: " . $zip->statusSys . "\n";
echo "filename: " . $zip->filename . "\n";
echo "comment: " . $zip->comment . "\n";
      */

      $zip->close();
      echo 'ok';
    }
    else
    {
      echo 'failed';
    }
  }

  /* creates a compressed zip file */
  public function create_zip($files = array(),$destination = '',$overwrite = false) 
  {
    //if the zip file already exists and overwrite is false, return false
    if(file_exists($destination) && !$overwrite) { return false; }
    //vars
    $valid_files = array();
    //if files were passed in...
    if(is_array($files)) {
      //cycle through each file
      foreach($files as $file) {
        //make sure the file exists
        if(file_exists($file)) {
          $valid_files[] = $file;
        }
      }
    }
    //if we have good files...
    if(count($valid_files)) {
      //create the archive
      $zip = new ZipArchive();
      if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
        return false;
      }
      //add the files
      foreach($valid_files as $file) {
        $zip->addFile($file,$file);
      }
      //debug
      //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
      
      //close the zip -- done!
      $zip->close();
      
      //check to make sure the file exists
      return file_exists($destination);
    }
    else
    {
      return false;
    }
  }

}
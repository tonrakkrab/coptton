<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ImageController;

use Illuminate\Support\Facades\Redirect;
use App\Schedules;
use App\Modules;
use App\Examinees;
use App\MDSections;
use App\MDSubSections;
use App\MDExams;
use App\ExamsEN;
use App\ExamsTH;
use App\ExamChoicesEN;
use App\ExamChoicesTH;
use App\PracticalExams;
use App\PracticalExamFiles;
use App\PracticalExamFileZips;
use App\PracticalExamResults;
use App\PracticalExamFileAnswers;
use File;
use Session;
use URL;
use Image;
use Storage;
use Input;
use App\Http\Requests\MDExamsRequest;
use App\Http\Requests\ImageRequest;
use App\Http\Requests\FileRequest;

use Auth;

use ZipArchive;


class UploadsManagerController extends Controller
{
  public function __construct()
  {
       // $this->middleware('admin');
  }
// Add the 4 methods below to the class
  /**
   * Create a new directory
   */
  public function createDirectory($folder)
  {
    $folder = $this->cleanFolder($folder);

    if ($this->disk->exists($folder)) {
      return "Folder '$folder' aleady exists.";
    }

    return $this->disk->makeDirectory($folder);
  }

  /**
   * Delete a directory
   */
  public function deleteDirectory($folder)
  {
    $folder = $this->cleanFolder($folder);

    $filesFolders = array_merge(
      $this->disk->directories($folder),
      $this->disk->files($folder)
      );
    if (! empty($filesFolders)) {
      return "Directory must be empty to delete it.";
    }

    return $this->disk->deleteDirectory($folder);
  }

  /**
   * Delete a file
   */
  public function deleteFile($path)
  {
    $path = $this->cleanFolder($path);

    if (! $this->disk->exists($path)) {
      return "File does not exist.";
    }

    return $this->disk->delete($path);
  }

  /**
   * Save a file
   */
  public function saveFile($path, $content)
  {
    $path = $this->cleanFolder($path);

    if ($this->disk->exists($path)) {
      return "File already exists.";
    }

    return $this->disk->put($path, $content);
  }

  public function uploadFileZipWordExam(FileRequest $fileZipWord, $id)
    { //Sitthinai 2016-08-11
      if(Request::hasFile('fileZip') != null)
      {                
        $fl = PracticalExamFileZips::findOrFail($id);                

        $file_input = Input::file('fileZip');
        $file_path = '/../../../../../../srv/ftp/files/word/exam/';
        $file_extension = $file_input->getClientOriginalExtension();
        //$file_name = 'word_zip' . $fl->id;
        $file_name = 'CoPTEST' . $fl->id;
        $file_description = 'ไฟล์ประกอบข้อสอบปฏิบัติ word processing';

        $fl->seq = 1;
        $fl->fileType = 'ZIP';
        $fl->filePath = $file_path;
        $fl->fileName = $file_name;
        $fl->fileExtension = $file_extension;
        $fl->fileDescription = $file_description;

        $path = public_path($file_path);
        Input::file('fileZip')->move($path, $file_name . '.' . $file_extension);

                //$fl->update();
        $fl->touch();                
        $fl->save(); 

        //Session::flash('message', 'File Zip Exam has been added');                
        return Redirect::back()->with("message", "File Zip Exam has been added.");
      }
      else
      {
        //Session::flash('error', 'File Zip Exam has not been added');
        return Redirect::back()->with("error", "File Zip Exam has not been added.");
      }
    }

  public function uploadFileWordExam(FileRequest $fileWord, $id)
    {   //Sitthinai 2016-08-11
      if(Request::hasFile('filePdf') != null)
      {                
        $fl = PracticalExamFiles::findOrFail($id);                

        $file_input = Input::file('filePdf');
        //$file_path = '/../storage/app/files/word/exam/';
        $file_path = '/../../../../../../srv/ftp/files/word/exam/';
        $file_extension = $file_input->getClientOriginalExtension();
        $file_name = 'word' . $fl->id;
        $file_description = 'ข้อสอบปฏิบัติ word processing';

        $fl->seq = 1;
        $fl->fileType = 'PDF';
        $fl->filePath = $file_path;
        $fl->fileName = $file_name;
        $fl->fileExtension = $file_extension;
        $fl->fileDescription = $file_description;

        $path = public_path($file_path);
        Input::file('filePdf')->move($path, $file_name . '.' . $file_extension);

        //$fl->update();
        $fl->touch();                
        $fl->save(); 

        //Session::flash('messeage', 'File Exam has been added');                
        return Redirect::back()->with("message", "File Exam has been added.");
      }
      else
      {
        //Session::flash('error', 'File Exam has not been added');
        return Redirect::back()->with("error", "File Exam has not been added.");
      }
    }


    public function uploadFileWordAnswer(FileRequest $fileWord, $id)
    {   //Sitthinai 2016-08-16
      if(Request::hasFile('filePdf') != null)
      {                
        $fl = PracticalExamFileAnswers::findOrFail($id);

        $file_input = Input::file('filePdf');
        //$file_path = '/../storage/app/files/word/answer/';
        $file_path = '/../../../../../../srv/ftp/files/word/answer/';
        $file_extension = $file_input->getClientOriginalExtension();
        $file_name = 'word_answer' . $fl->id;
        $file_description = 'คำตอบของข้อสอบปฏิบัติ word processing';

        $fl->seq = 1;
        $fl->fileType = 'PDF';
        $fl->filePath = $file_path;
        $fl->fileName = $file_name;
        $fl->fileExtension = $file_extension;
        $fl->fileDescription = $file_description;

        $path = public_path($file_path);
        Input::file('filePdf')->move($path, $file_name . '.' . $file_extension);

                //$fl->update();
        $fl->touch();                
        $fl->save(); 

        //Session::flash('message', 'File Answer has been added');                
        return Redirect::back()->with("message", "File Answer has been added.");    
      }
      else
      {
        //Session::flash('error', 'File Answer has not been added');
        return Redirect::back()->with("error", "File Answer has not been added.");
      }
    }

    public function uploadFileWordAnswerExaminee(FileRequest $fileZipWord, $id)    
    {   
      
      //$exam_scheduleid = $request->exam_schedule_id;
      //$exam_resultid = $request->exam_result_id;

      $ids = explode('|', $id);
      $exam_scheduleid = $ids[0];
      $exam_resultid = $ids[1];
/*
      var_dump(Request::hasFile('fileZip'));
      echo 'Sitthinai 2016-12-20'.'<br/>';
      echo 'exam_scheduleid: '.$exam_scheduleid.'<br/>';
      echo 'exam_resultid: '.$exam_resultid.'<br/>';      
      exit(0);
*/

      //$exam_result = PracticalExamResults::where('examineeID',$examinee_id)->where('examScheduleID',$schedule_id)->first();
      //$examResultFileAns = PracticalExamFileAnswers::where('exam_result_id',$id)->first();

      if(Request::hasFile('fileZip') != null)
      {
        //echo 'in if'; exit(0);
      
        //$fl = PracticalExamFileAnswers::findOrFail($ids[0]);
        //$fl = new PracticalExamFileAnswers();
        $fl = PracticalExamFileAnswers::where('exam_result_id',$exam_resultid)->first();
        $examResult = PracticalExamResults::findOrFail($exam_resultid);
        $exam = PracticalExams::findOrFail($examResult->examID);
        $examinee_info = Examinees::findOrFail($examResult->examineeID);
        //echo $examinee_info->examinee;exit(0);      

        //$user_name = Auth::user()->getAuthIdentifier();
        //user_name = 'pannita.j';
        $user_name = $examinee_info->examinee;

        $file_input = Input::file('fileZip');                
        $file_extension = $file_input->getClientOriginalExtension();               
        $file_name = 'answer_' . 'W' . str_pad(strval($exam_scheduleid), 4, '0', STR_PAD_LEFT) . '_' . $user_name . '_S' . $exam->seriesNo;        
        $file_description = 'คำตอบของข้อสอบปฏิบัติ word processing';
        $file_path = '/../../../../../../srv/ftp/files/word/answer/';

        $fl->exam_result_id = $exam_resultid;
        $fl->seq = 1;
        $fl->fileType = 'ZIP';
        $fl->filePath = $file_path;
        $fl->fileName = $file_name;
        $fl->fileExtension = $file_extension;
        $fl->fileDescription = $file_description;

        $path = public_path($file_path);
        Input::file('fileZip')->move($path, $file_name . '.' . $file_extension);
        
        $fl->touch();
        //$fl->save();
        $fl->update();

        Session::remove("error");
        Session::remove("info");
        Session::flash("message", "ส่งคำตอบเรียบร้อยแล้ว ขอบคุณสำหรับการทำข้อสอบ");

/*        //// return ไปหน้าแจ้งสถานะการอัพโหลด ////  */      
        return view('exams.practical.upload.show_status');

/*        /// return กลับหน้าตัวเอง ////
        $examResult= PracticalExamResults::findOrFail($exam_resultid);
        return view('exams.practical.exam_timeout',compact('examResult'));
*/

/*        //// return ไปหน้าหลักการทำข้อสอบ ////
        $modules = Modules::all();
        $examinee = Examinees::where('examinee',Auth::user()->getAuthIdentifier())->get()->first();

        $sch_e = $examinee->schedules;
        $arr_e_id = array();

        foreach ($sch_e as $s_e) {
            array_push($arr_e_id, $s_e->id);
        }
        
        $schedules_publish = Schedules::select()->whereNotIn('id',$arr_e_id)->get();
        $examResult = PracticalExamResults::where('examineeID', $examinee->id)->get();      
       
        return view('frontend.exam.index',compact('modules','examinee','schedules_publish','examResult')); 
*/
      }
      else
      {
        //$exam = PracticalExams::findOrFail($schedule_id);
        $examResult = PracticalExamResults::findOrFail($exam_resultid);

        Session::remove("message");        
        Session::flash("error", "ส่งคำตอบไม่สำเร็จ กรุณาส่งคำตอบอีกครั้ง");

        return view('exams.practical.exam_timeout',compact('examResult'));
      }     
    }

    public function uploadStatus(Request $request)
    {
        Session::remove("message");
        Session::remove("error");
        Session::flash("info", "หมดเวลาการส่งคำตอบ หากมีข้อสงสัยกรุณาติดต่อเจ้าหน้าที่");

        return view('exams.practical.upload.show_status');
    }
   
    
    public function uploadFileWordZipAnswerExaminee(FileRequest $fileZip, Request $request, $id)
    {   //Sitthinai 2016-10-02

      $file_path = '/../../../../../../srv/ftp/files/word/answer/';

      $ids = explode('|', $id);
        //var_dump($ids);exit(0);

      // Read directory path to Zip File
      if (File::isDirectory(public_path($file_path)))
      {             
        //Sample Usage : Add to zip file
        $all_file_text = $fileZip->input('allfile');
        $rtrim_all_file_text = rtrim($all_file_text,'|');    
        $arr_all_file = explode('|', $rtrim_all_file_text);    

        // foreach upload all file to server (directory answer)
        $path = public_path($file_path);
        for ($i=0; $i < count($arr_all_file); $i++) {
                       
          Input::file('fileZip')->move($path, $arr_all_file[$i]);
        }
        
        Session::flash('folderDone', 'Folder Word has been added');

        //return Redirect::back();
                
        $module_id = $ids[2];
        echo "moduleid: " . $module_id; exit(0);
        
        //$exam = PracticalExams::findOrFail(8); //8, 9, 10, 11, 30, 35, 36     // Modules 4, Word Processing
        $exam = PracticalExams::where('modules_id',$module_id)->first();
        //$exam = PracticalExams::where('modules_id',$modules_id)->get();        
        return view('exams/practical/display_examinee',compact('exam'));
      }          

      // Upload Zip file
      if(Request::hasFile('fileZip') != null)
      {                
        
        $fl = PracticalExamFileAnswers::findOrFail($ids[0]);

        $file_input = Input::file('fileZip');
        //$file_path = '/../storage/app/files/word/answer/';
        $file_path = '/../../../../../../srv/ftp/files/word/answer/';
        $file_extension = $file_input->getClientOriginalExtension();
        $file_name = 'word_answer_zip_' . $fl->id;
        $file_description = 'คำตอบของข้อสอบปฏิบัติ word processing';

        $fl->seq = 1;
        $fl->fileType = 'ZIP';
        $fl->filePath = $file_path;
        $fl->fileName = $file_name;
        $fl->fileExtension = $file_extension;
        $fl->fileDescription = $file_description;

        $path = public_path($file_path);
        Input::file('fileZip')->move($path, $file_name . '.' . $file_extension);

                //$fl->update();
        $fl->touch();                
        $fl->save(); 

        Session::flash('fileZipDone', 'File Zip has been added');                
        //return Redirect::back();
         
        $exam = PracticalExams::findOrFail($ids[1]);
        return view('exams.practical.display_examinee',compact('exam'));
      }
    }

  /**
   * Upload a folder
   */
  public function uploadFolderWordAnswer(FileRequest $fileWord, $id)
  {   //Sitthinai 2016-08-25

    $file_path = '/../storage/app/folders/word/answer/';
    //echo $file_path . "<br>"; 
    //var_dump(File::isDirectory(public_path($file_path))); 
    //echo "<br>"."test";exit(0);
    if (File::isDirectory(public_path($file_path)))
    {             
      //Sample Usage : Add to zip file
      $all_file_text = $fileWord->input('allfile');
      $rtrim_all_file_text = rtrim($all_file_text,'|');    
      $arr_all_file = explode('|', $rtrim_all_file_text);    

      setlocale(LC_ALL, 'th_TH.UTF-8');
      $zip = new ZipArchive;

      if ($zip->open('CoPTEST1_1st_test_2.tar', ZipArchive::CREATE) === TRUE) {  
        for ($i=0; $i < count($arr_all_file); $i++) {
          //echo 'File Name: '. $arr_all_file[$i].'<br>';
          $result = $zip->addFile(public_path('/_answer/CoPTEST1_1st/'. $arr_all_file[$i]), $arr_all_file[$i]);          
          //echo $result . '<br>';
        }
        $zip->close();          
      }
      else
      {
        echo 'failed';
      }      
      Session::flash('folderWordDone', 'Folder Word has been added');
      return Redirect::back();
    }          

    if(Request::hasFile('folder') != null)
    {
      $fl = PracticalExamFileAnswers::findOrFail($id);

      $file_input = Input::file('folder');
      $file_path = '/../storage/app/folders/word/answer/';
      $file_extension = $file_input->getClientOriginalExtension();
      $file_name = 'word_answer' . $fl->id;
      $file_description = 'คำตอบของข้อสอบปฏิบัติ word processing';

      $fl->seq = 1;
      $fl->fileType = 'PDF';
      $fl->filePath = $file_path;
      $fl->fileName = $file_name;
      $fl->fileExtension = $file_extension;
      $fl->fileDescription = $file_description;

      $path = public_path($file_path);
      Input::file('folder')->move($path, $file_name . '.' . $file_extension);

      //$fl->update();
      $fl->touch();                
      $fl->save(); 

      Session::flash('fileWordDone', 'File Word has been added');                
      return Redirect::back();        
    }
  }

  /**
  * Download a file
  */
  public function downloadFileWordExam(FileRequest $fileWord, $id)
  {   
    //Sitthinai 2016-11-03

    $file_path = '/../../../../../../srv/ftp/files/word/answer/';
    $path = public_path($file_path);
    //response()->download()

    Session::flash('fileWordDone', 'File Word has been added');                
    return Redirect::back();
  }

  /**
  * Download a folder
  */
  public function downloadFolderWordExam(FileRequest $fileWord, $id)
  {   //Sitthinai 2016-08-25
    if(Request::hasFile('filePdf') != null)
    {                
      $fl = PracticalExamFileAnswers::findOrFail($id);

      $file_input = Input::file('filePdf');
      $file_path = '/../storage/app/files/word/answer/';
      $file_extension = $file_input->getClientOriginalExtension();
      $file_name = 'word_answer' . $fl->id;
      $file_description = 'คำตอบของข้อสอบปฏิบัติ word processing';

      $fl->seq = 1;
      $fl->fileType = 'PDF';
      $fl->filePath = $file_path;
      $fl->fileName = $file_name;
      $fl->fileExtension = $file_extension;
      $fl->fileDescription = $file_description;

      $path = public_path($file_path);
      Input::file('filePdf')->move($path, $file_name . '.' . $file_extension);

                //$fl->update();
      $fl->touch();                
      $fl->save(); 

      Session::flash('fileWordDone', 'File Word has been added');                
      return Redirect::back();        
    }
  }
}

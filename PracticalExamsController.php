<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use Redirect;
use App\Schedules;
use App\Modules;
use App\Examinees;
use App\PracticalExams;
use App\PracticalExamOrders;
use App\PracticalExamSuborders;
use App\PracticalExamFileZips;
use App\PracticalExamFiles;
use App\PracticalExamFileAnswers;
use App\PracticalExamResults;

use Response;
use Auth;

class PracticalExamsController extends Controller
{


    // Sitthinai 2017-01-11, For Test Main Exam. Page
    // Route::get('examtest', 'PracticalExamsController@examtest');
    /* public function examtest() {

        $modules = Modules::all();
        $examinee = Examinees::where('examinee',Auth::user()->getAuthIdentifier())->get()->first();

        $sch_e = $examinee->schedules;
        $arr_e_id = array();        
        foreach ($sch_e as $s_e) {
            array_push($arr_e_id, $s_e->id);
        }        
        $schedules_publish = Schedules::select()->whereNotIn('id',$arr_e_id)->get(); 

        $examResult = PracticalExamResults::where('examineeID', $examinee->id)->get();    
        return view('frontend.exam.index_test',compact('modules','examinee','schedules_publish','examResult'));
    }*/

/*          //// Show Start-Finish DateTime  
            echo 'startTime : ' . $examResult->startTime . '<br>';
            echo 'finishTime : ' . date_format($examResult->finishTime, 'Y-m-d H:i:s') . '<br>';
            //exit(0);
            // $date->add(new \DateInterval('P7Y5M4DT4H3M2S'));     => 7 years, 5 months, 4 days, 4 hours, 3 minutes, 2 seconds
            // $date->format('Y-m-d H:i:s')                         => year-month-day hour:minute:second
*/

    ////////////////////////////////////////////////////////////////
    //////////////// -- Start - PractilExam Function -- ////////////////
    ////////////////////////////////////////////////////////////////

    // original from Controller: ExamineeController => function: postPracticalexamdisplay
    public function index(Request $request) {

        $exam = new PracticalExams();

        //$period = $request->period;       
        //$examinee_id = $request->examinee_id;
        //$schedule_id = $request->schedule_id;

        //echo Session::get('period');
        //echo Session::get('examinee_id');
        //echo Session::get('schedule_id');
        //exit(0);

/*        
        $examinee_id = Session::get('examinee_id');
        $schedule_id = Session::get('schedule_id');
        $period = Session::get('period');
        $period_schedule = Session::get('period_schedule');
        $until_stamp = Session::get('until_stamp');
*/
        $examinee_id = null;
        $schedule_id = null;
        $period = null;
        $period_schedule = null;
        $until_stamp = null;

/*
        session_start(); 
        if (isset($_SESSION['examinee_id']) && isset($_SESSION['schedule_id']) && isset($_SESSION['period'])
            && isset($_SESSION['period_schedule']) && isset($_SESSION['until_stamp']))
        {                                
            $examinee_id = $_SESSION['examinee_id'];
            $schedule_id = $_SESSION['schedule_id'];
            $period = $_SESSION['period'];
            $period_schedule = $_SESSION['period_schedule'];
            $until_stamp = $_SESSION['until_stamp'];
            
            var_dump($examinee_id);echo '<br/>';
            var_dump($schedule_id);echo '<br/>';
            var_dump($period);echo '<br/>';
            var_dump($period_schedule);echo '<br/>';
            var_dump($until_stamp);echo '<br/>';
*            
        }
*/
        

        //if ($request->period != null && $request->examinee_id != null & $request->schedule_id != null)
        //if (isset($request->period) && isset($request->examinee_id) & isset($request->schedule_id))
        if ($period == null && $examinee_id == null & $schedule_id == null)
        {
            //echo "in if true".'<br/>';

            $examinee_id = $request->examinee_id;
            $schedule_id = $request->schedule_id;
            $period = $request->period;
            $period_schedule = $request->period_schedule;
            $until_stamp = $request->until_stamp;

            //echo "before set Session".'<br/>';
/*            
            Session::flash('examinee_id', $request->examinee_id);
            Session::flash('schedule_id',$request->schedule_id);
            Session::flash('period', $request->period);
            Session::flash('period_schedule', $request->period_schedule);
            Session::flash('until_stamp', $request->until_stamp);
*/
            
            $_SESSION['examinee_id'] = $request->examinee_id;
            $_SESSION['schedule_id'] = $request->schedule_id;
            $_SESSION['period'] = $request->period;
            $_SESSION['period_schedule'] = $request->period_schedule;
            $_SESSION['until_stamp'] = $request->until_stamp;
            
            //echo "after set Session".'<br/>';
/*
            echo 'Session examinee_id: '. Session::get('examinee_id').'<br/>';
            echo 'Session schedule_id: '. Session::get('schedule_id').'<br/>';
            echo 'Session period: '. Session::get('period').'<br/>';
            echo 'Session period_schedule: '. Session::get('period_schedule').'<br/>';
            echo 'Session until_stamp: '. Session::get('until_stamp').'<br/>';
*/
        }
        else
        {
            //echo "in else".'<br/>';
            
            //$examinee_id = Session::get('examinee_id');
            //$schedule_id = Session::get('schedule_id');
            //$period = Session::get('period');
            //$period = Session::get('period_schedule');
            //$period = Session::get('until_stamp');

        }

        //exit(0);

        // Check is existing ExamResult
        $examResultAll = PracticalExamResults::where('examineeID', $examinee_id)
                        ->where('examScheduleID', $schedule_id)                        
                        ->get();
    
        $cnt_exam_result = $examResultAll->count();

        if ($cnt_exam_result > 0) 
        {
            $exam_result = $examResultAll->first();
            $exam= PracticalExams::where('id', $exam_result->examID)->get()->first();

            return view('exams/practical/display_examinee',compact('exam'),compact('exam_result'));
        }
        else
        {
            // Start - Random Select Exam
            $schedule = Schedules::findOrFail($schedule_id);

            //sitthinai.w 2017-02-14 for test
            //$schedule = Schedules::findOrFail(4);

            $module = $schedule->module;                
            $modules_id = $module[0]["attributes"]["id"];            
            $examAll = PracticalExams::where('modules_id',$modules_id)->get();
            
            $rand_exam_series = array_rand($examAll->toArray(), 1);
            $exam = $examAll[$rand_exam_series];
            // End - Random Select Exam           
            
            $examResult = new PracticalExamResults();
            $examResult->examineeID = $examinee_id;
            $examResult->examScheduleID = $schedule_id;
            $examResult->examID = $exam->id;        
           
            //$curDateTime = new \DateTime();
            //$examResult->startTime = $curDateTime;            
            //$examResult->finishTime = $curDateTime->add(new \DateInterval('PT'. $period . 'S'));

            $examResult->isDone = 'N';  // Y, N
            //enterScorePeople is null
            //enterScoreDate is null
            $examResult->isEnterScore = 'N';  // Y, N
            $examResult->totalScore = 0;
            $examResult->examResult = 'I';  // I, F, P, G
                    
            $examResult->save();

        
            $exam_result= PracticalExamResults::where('examineeID', $examinee_id)
                        ->where('examScheduleID', $schedule_id)->first();

            $examFileAns = new PracticalExamFileAnswers();            
            $examFileAns->exam_result_id = $exam_result->id;
            $examFileAns->save();

            return view('exams/practical/display_examinee',compact('exam'),compact('exam_result'));
   
        }
    }

     // original from Controller: ExamineeController => function: postPracticalexamdisplay
    public function index_dynamic(Request $request) {

        //echo "Practical Dynamic"; exit(0);

        $exam = new PracticalExams();

        $examinee_id = null;
        $schedule_id = null;
        $period = null;
        $period_schedule = null;
        $until_stamp = null;

        session_start(); 

        if (isset($_SESSION['schedule_id'])) {
            echo "<br/>Start => SESSION_schedule_id: ".$_SESSION['schedule_id'];
        }
        else {
            echo "<br/>SESSION_schedule_id not set";
        }

        echo ", request_schedule_id: ".$request->schedule_id;

/*        
        if (isset($_SESSION['examinee_id']) && isset($_SESSION['schedule_id']) && isset($_SESSION['period'])
            && isset($_SESSION['period_schedule']) && isset($_SESSION['until_stamp']))
        {                                
            $examinee_id = $_SESSION['examinee_id'];
            $schedule_id = $_SESSION['schedule_id'];
            $period = $_SESSION['period'];
            $period_schedule = $_SESSION['period_schedule'];
            $until_stamp = $_SESSION['until_stamp'];        
        }
*/
        
        if ($period == null && $examinee_id == null & $schedule_id == null)
        {            

            $examinee_id = $request->examinee_id;
            $schedule_id = $request->schedule_id;
            $period = $request->period;
            $period_schedule = $request->period_schedule;
            $until_stamp = $request->until_stamp;           
            
            $_SESSION['examinee_id'] = $request->examinee_id;
            $_SESSION['schedule_id'] = $request->schedule_id;
            $_SESSION['period'] = $request->period;
            $_SESSION['period_schedule'] = $request->period_schedule;
            $_SESSION['until_stamp'] = $request->until_stamp;

        }
        else
        {
            //echo "in else".'<br/>';
            
            //$examinee_id = Session::get('examinee_id');
            //$schedule_id = Session::get('schedule_id');
            //$period = Session::get('period');
            //$period = Session::get('period_schedule');
            //$period = Session::get('until_stamp');

        }

        //exit(0);

        // Check is existing ExamResult
        $examResultAll = PracticalExamResults::where('examineeID', $examinee_id)
                        ->where('examScheduleID', $schedule_id)                        
                        ->get();
    
        $cnt_exam_result = $examResultAll->count();

        if ($cnt_exam_result > 0) 
        {
            $exam_result = $examResultAll->first();
            $exam= PracticalExams::where('id', $exam_result->examID)->get()->first();

            echo "<br/>Final => SESSION_schedule_id: ".$_SESSION['schedule_id'];
            echo ", request_schedule_id: ".$request->schedule_id;

            return view('exams/practical/display_exam_dynamic',compact('exam'),compact('exam_result'));           
        }
        else
        {
            // Start - Random Select Exam
            $schedule = Schedules::findOrFail($schedule_id);

            //sitthinai.w 2017-02-14 for test
            //$schedule = Schedules::findOrFail(4);

            $module = $schedule->module;                
            $modules_id = $module[0]["attributes"]["id"];            
            $examAll = PracticalExams::where('modules_id',$modules_id)->get();
            
            $rand_exam_series = array_rand($examAll->toArray(), 1);
            $exam = $examAll[$rand_exam_series];
            // End - Random Select Exam

            $examResult = new PracticalExamResults();
            $examResult->examineeID = $examinee_id;
            $examResult->examScheduleID = $schedule_id;
            $examResult->examID = $exam->id;        
           
            //// set Start and Finish time for first click button 'Start Exam'
            //$curDateTime = new \DateTime();
            //$examResult->startTime = $curDateTime;            
            //$examResult->finishTime = $curDateTime->add(new \DateInterval('PT'. $period . 'S'));

            $examResult->isDone = 'N';  // Y, N
            //enterScorePeople is null
            //enterScoreDate is null
            $examResult->isEnterScore = 'N';  // Y, N
            $examResult->totalScore = 0;
            $examResult->examResult = 'I';  // I, F, P, G
                    
            $examResult->save();

        
            $exam_result= PracticalExamResults::where('examineeID', $examinee_id)
                        ->where('examScheduleID', $schedule_id)->first();

            $examFileAns = new PracticalExamFileAnswers();            
            $examFileAns->exam_result_id = $exam_result->id;
            $examFileAns->save();

            echo "<br/>Final => SESSION_schedule_id: ".$_SESSION['schedule_id'];
            echo ", request_schedule_id: ".$request->schedule_id;

            return view('exams/practical/display_exam_dynamic',compact('exam'),compact('exam_result'));
        }
    }
/*
    public function downloadExamZipFile(Request $request) {

        $fileZipName = $request->fileZipName;       
        $file_path = '/../../../../../../srv/ftp/files/word/exam/';
        $file = public_path() . $file_path . $fileZipName;
        
        $headers = array(
            'Content-Type: application/zip',    //Content-Type: application/octet-stream'
        );

        return Response::download($file, $fileZipName , $headers);        
    }
    */
    public function startexam(Request $request) {
        
        $examinee_id = $request->examinee_id;
        $schedule_id = $request->schedule_id;
        $period = $request->period;

        echo 'examineeid: '.$request->examinee_id.'<br/>';
        echo 'schedule_id: '.$request->schedule_id.'<br/>';        
        echo 'period: '.$request->period.'<br/>';
        //exit(0);

        $examResult = PracticalExamResults::where('examineeID', $examinee_id)
                        ->where('examScheduleID', $schedule_id)
                        ->get()->first();

        $examResult->isDone = 'N';
        $curDateTime = new \DateTime();
        $examResult->startTime = $curDateTime;            
        $examResult->finishTime = $curDateTime->add(new \DateInterval('PT'. $period . 'S'));

        //$examResult->touch();
        $examResult->update();


        $fileZipName = $request->fileZipName;       
        $file_path = '/../../../../../../srv/ftp/files/word/exam/';
        $file = public_path() . $file_path . $fileZipName;
        
        $headers = array(
            'Content-Type: application/zip',    //Content-Type: application/octet-stream'
        );

        return Response::download($file, $fileZipName , $headers);        
    }

    public function getCurServerDT() {

        return view('exams/practical/getdb/getCurServerDT');
    }

    public function getData(Request $request) {

        return view('exams/practical/getdb/getData');
    }

    public function timeout(Request $request) {

        $examinee_id = $request->examinees_id;
        $schedule_id = $request->schedules_id;        

        $examResult= PracticalExamResults::where('examineeID', $examinee_id)
                        ->where('examScheduleID', $schedule_id)
                        ->get()->first();

        $examResult->isDone = 'Y'; 
        //$examResult->touch();
        $examResult->update();

        return view('exams/practical/exam_timeout',compact('examResult'));
    }
    
    public function timeout_t(Request $request, $id) {
        
        $ids = explode('|', $id);
        $examinee_id = $ids[0];
        $schedule_id = $ids[1];

        $examResult= PracticalExamResults::where('examineeID', $examinee_id)
                        ->where('examScheduleID', $schedule_id)
                        ->get()->first();        

        $examResult->isDone = 'Y'; 
        //$examResult->touch();
        $examResult->update();

        return view('exams/practical/exam_timeout',compact('examResult'));
    }

    public function create(Request $request) {

        $module = Modules::findOrFail($request->id);

        $exam = new PracticalExams();
        $exam->isActive = true;        
        $module->practicalexams()->save($exam);

        $exam_update = PracticalExams::findOrFail($exam->id);

        $examFileZip = new PracticalExamFileZips();
        $examFileZip->exams_id = $exam->id;
        $exam_update->files()->save($examFileZip);

        $examFile = new PracticalExamFiles();
        $examFile->exams_id = $exam->id;
        $exam_update->files()->save($examFile);

        return Redirect::back();
    }

    public function show($id) {

        $exam = PracticalExams::findOrFail($id);
        return view('exams/practical/show',compact('exam'));
    }

    public function display($id) {

        $exam = PracticalExams::findOrFail($id);
        return view('exams/practical/display',compact('exam'));
    }
    ////////////////////////////////////////////////////////////////
    //////////////// -- End - PractilExam Function -- ////////////////
    ////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////
    //////////////// -- Start - Orrder Function -- ////////////////
    ////////////////////////////////////////////////////////////////
    public function suborder($id) {

        $order = PracticalExamOrders::findOrFail($id);
        $exam = PracticalExams::findOrFail($order->exams_id);
        return view('exams/practical/suborder',compact('order'),compact('exam'));
    }

    public function destroy($id) {

        $exam = PracticalExams::findOrFail($id);
        if($exam->orders->count()>0)
        {
            Session::flash('error', 'Cannot delete. This serie is not empty.');
            return Redirect::back();
        }
        $module_id = $exam->module->id;    
        $exam->delete();

        Session::flash('message', 'Deleted Serie '.$id.'.');
        return Redirect::to('acadmin/exams/'.$module_id);
    }

    public function store_order(Request $request) {

        $exam = PracticalExams::findOrFail($request->id);
        foreach($exam->orders as $order)
        {
            if($order->seq == $request->seq)
            {
                Session::flash('error', 'Cannot add sequence that already exists!');
                return Redirect::back();
            }
        }
        $order = new PracticalExamOrders();
        $order->seq = $request->seq;
        $order->exam_order = $request->orderTXT;

        $exam->orders()->save($order);

        return Redirect::back();
    }

    public function destroy_order($id) {

        $order = PracticalExamOrders::findOrFail($id);
        $order->delete();   

        return Redirect::back();
    }

    public function update_order(Request $request) {

        $order = PracticalExamOrders::findOrFail($request->orderid);
        //$order->seq = $request->orderseq
        $order->exam_order = $request->ordertxt;

        $order->touch();
        $order->save();

        return Redirect::back();
    }
    ////////////////////////////////////////////////////////////////
    //////////////// -- End - Orrder Function -- ////////////////
    ////////////////////////////////////////////////////////////////



    ////////////////////////////////////////////////////////////////
    //////////////// -- Start - Suborder Function -- ////////////////
    ////////////////////////////////////////////////////////////////
    public function store_suborder(Request $request) {

        $examOrder = PracticalExamOrders::findOrFail($request->id);
        foreach($examOrder->suborders as $suborder)
        {
            if($suborder->seq == $request->seq)
            {
                Session::flash('error', 'Cannot add sequence that already exists!');
                return Redirect::back();
            }
        }
        $suborder = new PracticalExamSuborders();
        $suborder->seq = $request->seq;
        $suborder->exam_order = $request->orderTXT;

        $examOrder->suborders()->save($suborder);

        return Redirect::back();
    }
    
    public function destroy_suborder($id) {

        $suborder = PracticalExamSuborders::findOrFail($id);
        $suborder->delete();   

        return Redirect::back();
    }

    public function update_suborder(Request $request) {

        $suborder = PracticalExamSuborders::findOrFail($request->suborderid);               
        //$suborder->seq = $request->orderseq
        $suborder->exam_order = $request->ordertxt;

        $suborder->touch();
        $suborder->save();

        return Redirect::back();
    }
    ////////////////////////////////////////////////////////////////
    //////////////// -- End - Suborder Function -- ////////////////
    ////////////////////////////////////////////////////////////////



    ////////////////////////////////////////////////////////////////
    //////////////// -- Start - Score Function -- ////////////////
    ////////////////////////////////////////////////////////////////
    public function scores(Request $request) {

        $schedules = Schedules::all()->sortByDesc('id');
        $modules = Modules::all()->sortBy('moduleID');

        return view('exams/scores/index',compact('schedules','modules'));
    }
    ////////////////////////////////////////////////////////////////
    //////////////// -- End - Score Function -- ////////////////
    ////////////////////////////////////////////////////////////////


}
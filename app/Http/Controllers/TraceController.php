<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use MongoDate;
// use MongoId;

use Redis;

use App\Jobs\Testjob;

class TraceController extends Controller
{
    private $django_ip;
    private $validation_error_filename;

    public function __construct(){
        $this->django_ip = '192.168.1.225';
        $this->validation_error_filename = '/var/www/html/ethereum_BIS_web/storage/logs/validation_error.txt';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

        return view('trace_index_page');
    }

    public function list_asset_main_leager_site(Request $request){
        $trace_asset_in_leagersite_datas = DB::collection('trace_asset_in_leagersite')
                                          ->where('asset_RFID', $request->input("asset_ID_value"))
                                          ->get();

        $leager_site_data = [];
        $leager_site_table_data = [];

        if(count($trace_asset_in_leagersite_datas) > 0){
            $trace_asset_in_leagersite_datas = $trace_asset_in_leagersite_datas[0];

            $trace_asset_in_leagersite_datas_jaon = $this->validation_trace_asset_in_leagersite_table($trace_asset_in_leagersite_datas);
            $trace_asset_in_leagersite_datas_jaon_decode = json_decode($trace_asset_in_leagersite_datas_jaon, true);
            $file = fopen($this->validation_error_filename,"a+");
            if((strcmp($trace_asset_in_leagersite_datas_jaon_decode['error'],'ok')!=0)){
                //只會回填系統錯誤，不會回填error log檔，不然會太複雜，需要回填error可以可用手動驗證
                fwrite($file,"trace_asset_in_leagersite 資料表被篡改\n");
                fwrite($file,"error_message:".$trace_asset_in_leagersite_datas_jaon_decode['error']."\n");
            }
            fclose($file);
            for($count_trace_asset_switch_site_array=0;$count_trace_asset_switch_site_array<count($trace_asset_in_leagersite_datas['leager_site_id_array']);$count_trace_asset_switch_site_array++){
                $leager_site_data = DB::collection('leager_site')
                                                ->where('_id', $trace_asset_in_leagersite_datas['leager_site_id_array'][$count_trace_asset_switch_site_array])
                                                ->get();
                if(count($leager_site_data) >0){
                    $leager_site_data = $leager_site_data[0];
                }else{
                    $leager_site_data = [];
                    $leager_site_data['_id'] = "null";
                    $leager_site_data['leager_site_name'] = "null";
                    $leager_site_data['leager_site_createtime'] = "null";
                    $leager_site_data['leager_site_updatetime'] = "null";
                }
                    
                $leager_site_table_data[] = array(  "id" => urlencode((string)$leager_site_data['_id']),
                                                    "leager_site_name" => urlencode($leager_site_data['leager_site_name']),
                                                    "leager_site_createtime" => $leager_site_data['leager_site_createtime'],
                                                    "leager_site_updatetime" => $leager_site_data['leager_site_updatetime'],
                                                    "leager_site_validation_status" => urlencode($trace_asset_in_leagersite_datas['error'][$count_trace_asset_switch_site_array][count($trace_asset_in_leagersite_datas['error'][$count_trace_asset_switch_site_array])-1]),
                                                    "leager_site_error_item" => $trace_asset_in_leagersite_datas['error_item'][$count_trace_asset_switch_site_array],
                                                    "leager_site_error_time" => $trace_asset_in_leagersite_datas['error_time'][$count_trace_asset_switch_site_array][count($trace_asset_in_leagersite_datas['error_time'][$count_trace_asset_switch_site_array])-1],
                );
            }
        }

        $error_str = "";
        //判斷是否有該檔案
        if(file_exists($this->validation_error_filename)){
            $file = fopen($this->validation_error_filename, "r");
            if($file != NULL){
                //當檔案未執行到最後一筆，迴圈繼續執行(fgets一次抓一行)
                while (!feof($file)) {
                    $error_str .= fgets($file);
                }
                fclose($file);
            }
        }

        return view('trace_leager_site_page', [
            'leager_site_table_data' => $leager_site_table_data,
            'asset_RFID_ID' => $request->input("asset_ID_value"),
            'error_str' => str_replace(array("\r\n", "\n\r", "\r", "\n"), 'text_return', $error_str)
        ]);
    }

    public function get_switch_site_data(Request $request){
        $switch_site_datas = DB::collection('switch_site')
                                ->where('leager_site', $request->input("leager_site_id"))
                                ->get();
    
        $error_str = "";
        $switch_site_table_data = [];
        if(count($switch_site_datas) > 0){
            //只取第二筆資料，因為第一筆資料為進場資料
            $create_time = '';
            // $set_sensor_group_array_pos = 0;
            $file = fopen($this->validation_error_filename,"a+");
            for($count_switch_site_datas=0;$count_switch_site_datas<count($switch_site_datas);$count_switch_site_datas++){
                $switch_site_data_validation_result = $this->validation_switch_site_table($switch_site_datas[$count_switch_site_datas]);
                $switch_site_data_validation_result_decode = json_decode($switch_site_data_validation_result, true);
                if((strcmp($switch_site_data_validation_result_decode['error'],'ok')!=0)){
                    //只會回填系統錯誤，不會回填error log檔，不然會太複雜，需要回填error可以可用手動驗證
                    fwrite($file,"switch_site 資料表被篡改\n");
                    fwrite($file,"switch_site 資料表被篡改"+$switch_site_datas[$count_switch_site_datas]['_id']+"\n");
                    fwrite($file,"error_message:".$switch_site_data_validation_result_decode['error']."\n");
                }

                if(($count_switch_site_datas%2)==1){
                    $switch_site_table_data[] = [];
                    $sensor_group_datas = DB::collection('sensor_group')
                                    ->where('leager_site', $request->input("leager_site_id"))
                                    ->where('asset_RFID_array', $switch_site_datas[$count_switch_site_datas]['asset_RFID_array'][0])
                                    ->get()[0];

                    $sensor_group_data_validation_result = $this->validation_sensor_group_table($sensor_group_datas);
                    $sensor_group_data_validation_result_decode = json_decode($sensor_group_data_validation_result, true);
                    if((strcmp($sensor_group_data_validation_result_decode['error'],'ok')!=0)){
                        fwrite($file,"sensor_group 資料表被篡改\n");
                        fwrite($file,"sensor_group 資料表被篡改"+$sensor_group['_id']+"\n");
                    }
                    for($count_switch_site_data_asset_RFID=0;$count_switch_site_data_asset_RFID<count($switch_site_datas[$count_switch_site_datas]['asset_RFID_array']);$count_switch_site_data_asset_RFID++){
                        $switch_site_table_data[$count_switch_site_datas][] = array(  "leager_site" => urlencode((string)$switch_site_datas[$count_switch_site_datas]['leager_site']),
                                                                                        "camera_id_array" => $sensor_group_datas['sensor_camera_group'][$count_switch_site_data_asset_RFID],
                                                                                        "video_IPFS_hash_array" => $switch_site_datas[$count_switch_site_datas]['video_IPFS_hash_array'][$count_switch_site_data_asset_RFID],
                                                                                        "sensor_ethereum_blockchain_tx_array" => $switch_site_datas[$count_switch_site_datas]['sensor_ethereum_blockchain_tx_array'][$count_switch_site_data_asset_RFID],
                                                                                        "asset_RFID" => $switch_site_datas[$count_switch_site_datas]['asset_RFID_array'][$count_switch_site_data_asset_RFID],
                                                                                        "ldr_id_array" => $sensor_group_datas['sensor_ldr_group'][$count_switch_site_data_asset_RFID],
                                                                                        "start_time" => $create_time,
                                                                                        "end_time" => $switch_site_datas[$count_switch_site_datas]['update_time']);
                    }
                    // $set_sensor_group_array_pos++;            
                }else{
                    $create_time = $switch_site_datas[$count_switch_site_datas]['update_time'];
                    //測試Bson時間戳正則化
                    // print_r($switch_site_data['update_time']);
                    // $utcdatetime = (string)$switch_site_data['update_time'];
                    // print_r(date('Y-m-d H:i:s', (((int)((string)$utcdatetime))/1000)));
                    // echo "<br>";
                }
            }
            fclose($file);
            //判斷是否有該檔案
            if(file_exists($this->validation_error_filename)){
                $file = fopen($this->validation_error_filename, "r");
                if($file != NULL){
                    //當檔案未執行到最後一筆，迴圈繼續執行(fgets一次抓一行)
                    while (!feof($file)) {
                        $error_str .= fgets($file);
                    }
                    fclose($file);
                }
            }
        }

        // print_r($switch_site_table_data);

        return view('trace_switch_site_page', [
            'switch_site_table_datas' => $switch_site_table_data,
            'error_str' => str_replace(array("\r\n", "\n\r", "\r", "\n"), 'text_return', $error_str)
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_validation_data(Request $request){
        // print_r('leager_site_id:'.$request->input("leager_site_id"));
        // echo "<br>";
        // print_r('asset_ID:'.$request->input("asset_ID"));
        // echo "<br>";
        // print_r('ldr_ID:'.$request->input("ldr_ID"));
        // echo "<br>";
        // print_r('camera_ID:'.$request->input("camera_ID"));
        // echo "<br>";
        // print_r('start_time:'.$request->input("start_time"));
        // echo "<br>";
        // print_r('end_time:'.$request->input("end_time"));
        // echo "<br>";
        // print_r('sensor_group_number:'.$request->input("sensor_group_number"));
        // echo "<br>";
        $validation_hight_frequency_data = $this->validation_hight_frequency_data( 
            $request->input("leager_site_id"),
            $request->input("asset_ID"),
            $request->input("ldr_ID"),
            $request->input("camera_ID"),
            $request->input("start_time"),
            $request->input("end_time"),
            $request->input("sensor_group_number")
        );

        // if((count($validation_hight_frequency_data['img_list']) != 0)){
            $img_list_json_decode = json_decode($validation_hight_frequency_data['img_list'], true);
            //驗證有錯誤需要在這邊處理
            return view('trace_validation_data', [
                'ldr_data_array' => $validation_hight_frequency_data['ldr_data_array'],
                'yolo_tag_array' => $validation_hight_frequency_data['yolo_tag_string'],
                'video_IPFS_hash' => $validation_hight_frequency_data['video_IPFS_hash_array'],
                'time_array' => $validation_hight_frequency_data['time_array'],
                'trace_item_detail_array' => $img_list_json_decode['item_error'],
                'leager_site_id' => $request->input("leager_site_id"),
                'asset_ID' => $request->input("asset_ID"),
                'camera_ID' => $request->input("camera_ID"),
                'start_time' => $request->input("start_time"),
                'end_time' => $request->input("end_time"),
                'sensor_group_number' => $request->input("sensor_group_number")
            ]);

        // }else{
        //     return view('trace_validation_data', [
        //         'error_message' => '節點未啟用'
        //     ]);
        // }
    }

    function get_video_data(Request $request){
        $validation_low_frequency_data = $this->validation_low_frequency_data(  $request->input("leager_site_id"), 
                                                                                $request->input("asset_ID"), 
                                                                                $request->input("ldr_ID"),
                                                                                $request->input("camera_ID"), 
                                                                                $request->input("start_time"), 
                                                                                $request->input("end_time"), 
                                                                                $request->input("video_IPFS_hash"),
                                                                                $request->input("sensor_group_number"));
        // if((count($validation_low_frequency_data['img_list']) != 0)){
            $img_list_json_decode = json_decode($validation_low_frequency_data['img_list'], true);
            //做前端顯示對應
            $IPFS_blade_frame_hash_string = "";
            $count_bundle_array = [];
            for($count_frame_blade_hash_string=0;$count_frame_blade_hash_string<count($validation_low_frequency_data['frame_blade_hash_string']);$count_frame_blade_hash_string++){
                array_push($count_bundle_array,count(explode('-',$validation_low_frequency_data['frame_blade_hash_string'][$count_frame_blade_hash_string])));
            }
            $count_IPFS_frame_hash=0;
            for($count_count_bundle_array=0;$count_count_bundle_array<count($count_bundle_array);$count_count_bundle_array++){
                if($count_IPFS_frame_hash != 0){
                    $IPFS_blade_frame_hash_string = $IPFS_blade_frame_hash_string.'+';
                }
                for($bundle_array_count=0;$bundle_array_count<$count_bundle_array[$count_count_bundle_array];$bundle_array_count++){
                    if($bundle_array_count == 0){
                        $IPFS_blade_frame_hash_string = $IPFS_blade_frame_hash_string.$img_list_json_decode['IPFS_frame_hash'][$count_IPFS_frame_hash];
                    }else{
                        $IPFS_blade_frame_hash_string = $IPFS_blade_frame_hash_string.'-'.$img_list_json_decode['IPFS_frame_hash'][$count_IPFS_frame_hash];
                    }
                    $count_IPFS_frame_hash++;
                }
            }
            //驗證有錯誤需要在這邊處理
            return view('trace_validation_video_data', [
                'video_path' => url('/').'/'.$validation_low_frequency_data['video_name'],
                'frame_hash_string' => $validation_low_frequency_data['frame_blade_hash_string'],
                'yolo_tag_string' => $validation_low_frequency_data['yolo_blade_tag_string'],
                // 'video_validation' => $img_list_json_decode['error'],
                'trace_item_detail_array' => $img_list_json_decode['item_error'],
                'IPFS_frame_hash_array' => explode('+',$IPFS_blade_frame_hash_string),
                'time_array' => $validation_low_frequency_data['time_array'],
                'error_message' => ''
            ]);
        // }else{
        //     // print_r('笑笑就好2');
        //     return view('trace_validation_video_data', [
        //         'video_validation' => '不知名錯誤',
        //         'error_message' => '不知名錯誤'
        //     ]);
        // }
     }

     function manual_trigger_background_job(Request $request){
        //$request->input("leager_site_pos")

        $leager_site_table_data_id = $request->input("leager_site_table_data_id");
        $asset_RFID_ID = $request->input("asset_RFID_ID");

        // print_r($request->input("leager_site_table_data_id"));
        // print_r($request->input("asset_RFID_ID"));

        // $leager_site_table_data_id = "5ebbe7949f78fc20bab75a86";
        // $asset_RFID_ID = "tea1";

        // $leager_site_table_data_id = "5ebbe7949f78fc20bab75a89";
        // $asset_RFID_ID = "tea1";

        $switch_site_datas = DB::collection('switch_site')
                                ->where('leager_site', $leager_site_table_data_id)
                                ->where('asset_RFID_array', $asset_RFID_ID)
                                ->get();

        $trace_asset_in_leagersite = DB::collection('trace_asset_in_leagersite')
                                        ->where('asset_RFID', $asset_RFID_ID)
                                        ->get()[0];

        $recode_cur_switch_site_pos = 0;
        for($count_asset_switch_site_array=0;$count_asset_switch_site_array<count($trace_asset_in_leagersite['leager_site_id_array']);$count_asset_switch_site_array++){
            if(strcmp($trace_asset_in_leagersite['leager_site_id_array'][$count_asset_switch_site_array],$leager_site_table_data_id) == 0){
                $recode_cur_switch_site_pos = $count_asset_switch_site_array;
            }
        }

        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $client->post('http://'.$this->django_ip.':8000/check_hmac/process_background_trace_back_job_table/create_background_data',[
            'json' => [
            'leager_site'=>$leager_site_table_data_id,
            'error_pos'=>$recode_cur_switch_site_pos,
            'switch_site_login_id'=> (string)$switch_site_datas[0]['_id'],
            'switch_site_logout_id'=> (string)$switch_site_datas[1]['_id'],
            'csrf_name' => csrf_token(),
        ]])->getBody();
        
        dispatch(new Testjob(["book_id"=>'測試背景驗證']));

        // dispatch(new Testjob());
        // $connection = null;
        // $default = 'default';
        // var_dump( \Queue::getRedis()->connection($connection)->zrange('queues:'.$default.':delayed' ,0, -1) );
        // var_dump( \Queue::getRedis()->connection($connection)->zrange('queues:'.$default.':delayed' ,0, -1) );
        // var_dump(Redis::get("HASH:horizon:68"));
        // var_dump(Redis::exists("HASH:horizon:68"));
        // var_dump(Redis::connection($connection)->zrange('horizen:recent_jobs' ,0, -1));
        // var_dump(Redis::connection($connection)->zrange('horizen:failed_jobs' ,0, -1));
     }

     function validation_hight_frequency_data($leager_site_id, $asset_ID, $ldr_ID, $camera_ID, $start_time, $end_time, $sensor_group_pos){

        //test 資料
        $RFID_SENSOR = $asset_ID;
        // print_r(Carbon::createFromDate(2015, 4, 1));
        // print_r(floor((((int)((string)$start_time))/1000)));
        // print_r(floor((((int)((string)$end_time))/1000)));
        // php要取得毫秒與微秒換算成時間格式需要再查看
        // $start_date_time = new DateTime(date('Y-m-d H:i:s.u', (((int)((string)$start_time))/1000)));
        // $end_date_time = new DateTime(date('Y-m-d H:i:s.u', (((int)((string)$end_time))/1000)));
        // $start_mongodate_time = new MongoDate(strtotime(date('Y-m-d H:i:s', (((int)((string)$start_time))/1000))));
        // $end_mongodate_time = new MongoDate(strtotime(date('Y-m-d H:i:s', (((int)((string)$end_time))/1000))));
        // print_r($start_mongodate_time);

        //如果使用毫秒
        // $start_date_time = new DateTime(date('Y-m-d H:i:s', (((int)((string)$start_time))/1000)));
        // $end_date_time = new DateTime(date('Y-m-d H:i:s', (((int)((string)$end_time))/1000)));
        $start_date_time = $start_time;
        $end_date_time = $end_time;
        $res = DB::collection('realtime_sensor_data')
                ->where('asset_RFID', $RFID_SENSOR)
                ->where('ldr_id', $ldr_ID)
                ->where('camera_id', $camera_ID)
                ->whereBetween('update_time', array($start_date_time,$end_date_time))
                ->get();
        // dd($res);
        $ldr_data_array = [];
        $yolo_tag_array = [];
        $frame_hash_array = [];
        $leager_site_array = [];
        $time_array = [];
        //取得IOT資料，並儲存陣列
        for($count_realtime_sensor_data=0;$count_realtime_sensor_data<count($res);$count_realtime_sensor_data++){
            //光敏電阻資料
            array_push($ldr_data_array, $res[$count_realtime_sensor_data]['ldr_data']);
            //yolo tag資料
            array_push($yolo_tag_array, $res[$count_realtime_sensor_data]['yolo_tag']);
            //frame資料
            array_push($frame_hash_array, $res[$count_realtime_sensor_data]['frame_hash']);
            //每筆資料的id
            array_push($leager_site_array, ((string)$res[$count_realtime_sensor_data]['_id']));

            array_push($time_array, ((string)$res[$count_realtime_sensor_data]['update_time']));
        }

        //mongo_ID資料前處理
        $mongo_ID_string = "";
        for($count_mongoID_data_array=0;$count_mongoID_data_array<count($leager_site_array);$count_mongoID_data_array++){
            if($count_mongoID_data_array == 0){
                $mongo_ID_string = $mongo_ID_string.$leager_site_array[$count_mongoID_data_array];
            }else{
                $mongo_ID_string = $mongo_ID_string.'-'.$leager_site_array[$count_mongoID_data_array];
            }
        }

        //光敏電阻資料前處理
        $ldr_data_string = "";
        for($count_ldr_data_array=0;$count_ldr_data_array<count($ldr_data_array);$count_ldr_data_array++){
            //做每個陣列的分界
            if($count_ldr_data_array != 0){
                $ldr_data_string = $ldr_data_string.'+';
            }
            for($count_ldr_data_array_in=0;$count_ldr_data_array_in<count($ldr_data_array[$count_ldr_data_array]);$count_ldr_data_array_in++){
                //第一筆比較特別
                if($count_ldr_data_array_in == 0){
                    $ldr_data_string = $ldr_data_string.$ldr_data_array[$count_ldr_data_array][$count_ldr_data_array_in];
                }else{
                    $ldr_data_string = $ldr_data_string.'-'.$ldr_data_array[$count_ldr_data_array][$count_ldr_data_array_in];
                }
            }
        }

        //yolo tag資料，每筆資料會是陣列
        $yolo_tag_string = "";
        for($count_yolo_tag_array_out=0;$count_yolo_tag_array_out<count($yolo_tag_array);$count_yolo_tag_array_out++){
            //做每個陣列的分界
            if($count_yolo_tag_array_out != 0){
                $yolo_tag_string = $yolo_tag_string.'+';
            }
            for($count_yolo_tag_array_in=0;$count_yolo_tag_array_in<count($yolo_tag_array[$count_yolo_tag_array_out]);$count_yolo_tag_array_in++){
                //第一筆比較特別
                if($count_yolo_tag_array_in == 0){
                    $yolo_tag_string = $yolo_tag_string.$yolo_tag_array[$count_yolo_tag_array_out][$count_yolo_tag_array_in];
                }else{
                    $yolo_tag_string = $yolo_tag_string.'-'.$yolo_tag_array[$count_yolo_tag_array_out][$count_yolo_tag_array_in];
                }
            }
        }

        //frame資料，每筆資料會是陣列
        $frame_hash_string = "";
        for($count_frame_hash_array_out=0;$count_frame_hash_array_out<count($frame_hash_array);$count_frame_hash_array_out++){
            //做每個陣列的分界
            if($count_frame_hash_array_out != 0){
                $frame_hash_string = $frame_hash_string.'+';
            }
            for($count_frame_hash_array_in=0;$count_frame_hash_array_in<count($frame_hash_array[$count_frame_hash_array_out]);$count_frame_hash_array_in++){
                //第一筆比較特別
                if($count_frame_hash_array_in == 0){
                    $frame_hash_string = $frame_hash_string.$frame_hash_array[$count_frame_hash_array_out][$count_frame_hash_array_in];
                }else{
                    $frame_hash_string = $frame_hash_string.'-'.$frame_hash_array[$count_frame_hash_array_out][$count_frame_hash_array_in];
                }
            }
        }

        //取得轉場資料，只取離場的，因為只有離場的整個廠景的資料，差一分鐘審略秒過後的時間單位
        //如果有毫秒的話
        //$end_date_time2 = new DateTime(date('Y-m-d H:i:s', ((((int)((string)$end_time))+1200)/1000)));
        // $res2 = DB::collection('switch_site')
        //         ->where('leager_site', $leager_site_id)
        //         ->whereBetween('update_time', array($end_date_time,$end_date_time2))
        //         ->get()[0];
        //處理到秒數

        $res2 = DB::collection('switch_site')
                ->where('leager_site', $leager_site_id)
                ->where('update_time', $end_date_time)
                ->get()[0];
        // print_r($end_date_time);
        // echo "<br>";
        // print_r($leager_site_id);
        // dd($end_date_time);

        // $sensor_group_data = DB::collection('sensor_group')
        //                         ->where('leager_site', $leager_site_id)
        //                         ->where('asset_RFID_array', $RFID_SENSOR)
        //                         ->get()[0];
        
        // $sensor_group_pos = 0; 
        // for($count_sensor_group_data=0;$count_sensor_group_data<count($sensor_group_data['asset_RFID_array']);$count_sensor_group_data++){
        //     if(strcmp($sensor_group_data['asset_RFID_array'][$count_sensor_group_data],$RFID_SENSOR)==0){
        //         $sensor_group_pos = $count_sensor_group_data;
        //         break;
        //     } 
        // }

        //場景中有哪些資產，可以得知此場景，此對時間內有什麼樣的物品，越接近後面越是完成品或相同的物品
        // print_r($res2['asset_RFID_array'][0]);
        //之後會是一個設定值，一個資產會掛載幾項IOT
        // $group_iot_item_number = 2;
        // $cur_item_pos = floor(count($res2['sensor_ethereum_blockchain_tx_array'])/$group_iot_item_number);
        //場景中有哪些交易
        $blockchain_tx_string = "";
        for($count_blockchain_tx_array_out=0;$count_blockchain_tx_array_out<count($res2['sensor_ethereum_blockchain_tx_array'][$sensor_group_pos]);$count_blockchain_tx_array_out++){
            //做每個陣列的分界
            // print_r($res2['sensor_ethereum_blockchain_tx_array'][$count_blockchain_tx_array_out]);
            // echo "<br>";
            if($count_blockchain_tx_array_out == 0){
                $blockchain_tx_string = $blockchain_tx_string.$res2['sensor_ethereum_blockchain_tx_array'][$sensor_group_pos][$count_blockchain_tx_array_out];
            }else{
                $blockchain_tx_string = $blockchain_tx_string.'-'.$res2['sensor_ethereum_blockchain_tx_array'][$sensor_group_pos][$count_blockchain_tx_array_out];
            }
        }

        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $client->post('http://'.$this->django_ip.':8000/ethereum_site/process_ethereum_data/main',[
            'json' => [
            'asset_ID'=>$RFID_SENSOR,
            'test_iot_data_array'=>$ldr_data_string,
            'test_yolo_tag_array'=> $yolo_tag_string,
            'test_frame_hash_array'=> $frame_hash_string,
            'tx_hex_data_array'=> $blockchain_tx_string,
            'mongoID_data_array'=> $mongo_ID_string,
            'csrf_name' => csrf_token(),
        ]])->getBody();

        //驗證回來的地方
        //count($img_list) != 0 是代表回傳有值，有可能發生奇怪的錯誤而無回傳值
        //$img_list_json_decode['check'] == 1 確認內部驗證無誤
        $validation_hight_frequency_data = [];
        $validation_hight_frequency_data['ldr_data_array'] = explode('+',$ldr_data_string);
        $validation_hight_frequency_data['yolo_tag_string'] = explode('+',$yolo_tag_string);
        $validation_hight_frequency_data['video_IPFS_hash_array'] = $res2['video_IPFS_hash_array'][$sensor_group_pos];
        $validation_hight_frequency_data['time_array'] = $time_array;
        $validation_hight_frequency_data['img_list'] = $response;

        return $validation_hight_frequency_data;
     }

     function validation_low_frequency_data( $leager_site_id, $asset_ID, $ldr_ID, $camera_ID, $start_time, $end_time, $video_hash, $sensor_group_number){
        //如果有毫秒的話
        // $start_date_time = new DateTime(date('Y-m-d H:i:s', (((int)((string)$start_time))/1000)));
        // $end_date_time = new DateTime(date('Y-m-d H:i:s', (((int)((string)$end_time))/1000)));
        //如果只有秒數的話
        // print_r('leager_site_id:'.$leager_site_id);
        // echo "<br>";
        // print_r('asset_ID:'.$asset_ID);
        // echo "<br>";
        // print_r('camera_ID:'.$camera_ID);
        // echo "<br>";
        // print_r('start_time:'.$start_time);
        // echo "<br>";
        // print_r('end_time:'.$end_time);
        // echo "<br>";
        // print_r('video_hash:'.$video_hash);
        // echo "<br>";
        // print_r('sensor_group:'.$sensor_group);
        // echo "<br>";
        $start_date_time = $start_time;
        $end_date_time = $end_time;
        $res = DB::collection('realtime_sensor_data')
                ->where('asset_RFID', $asset_ID)
                ->where('camera_id', $camera_ID)
                ->whereBetween('update_time', array($start_date_time,$end_date_time))
                ->get();

        $frame_hash_array = [];
        $yolo_tag_array = [];
        $time_array = [];
        //取得IOT資料，並儲存陣列
        for($count_realtime_sensor_data=0;$count_realtime_sensor_data<count($res);$count_realtime_sensor_data++){
            //frame資料
            array_push($frame_hash_array, $res[$count_realtime_sensor_data]['frame_hash']);

            array_push($yolo_tag_array, $res[$count_realtime_sensor_data]['yolo_tag']);

            array_push($time_array, $res[$count_realtime_sensor_data]['update_time']);
        }

        //yolo tag資料，每筆資料會是陣列
        $yolo_blade_tag_string = "";
        for($count_yolo_tag_array_out=0;$count_yolo_tag_array_out<count($yolo_tag_array);$count_yolo_tag_array_out++){
            //做每個陣列的分界
            if($count_yolo_tag_array_out != 0){
                $yolo_blade_tag_string = $yolo_blade_tag_string.'+';
            }
            for($count_yolo_tag_array_in=0;$count_yolo_tag_array_in<count($yolo_tag_array[$count_yolo_tag_array_out]);$count_yolo_tag_array_in++){
                //第一筆比較特別
                if($count_yolo_tag_array_in == 0){
                    $yolo_blade_tag_string = $yolo_blade_tag_string.$yolo_tag_array[$count_yolo_tag_array_out][$count_yolo_tag_array_in];
                }else{
                    $yolo_blade_tag_string = $yolo_blade_tag_string.'-'.$yolo_tag_array[$count_yolo_tag_array_out][$count_yolo_tag_array_in];
                }
            }
        }

        //frame資料，每筆資料會是陣列
        $frame_hash_string = "";
        $frame_blade_hash_string = "";
        for($count_frame_hash_array_out=0;$count_frame_hash_array_out<count($frame_hash_array);$count_frame_hash_array_out++){
            //做每個陣列的分界，影片部分需要串接
            if($count_frame_hash_array_out != 0){
                $frame_blade_hash_string = $frame_blade_hash_string.'+';
            }
            for($count_frame_hash_array_in=0;$count_frame_hash_array_in<count($frame_hash_array[$count_frame_hash_array_out]);$count_frame_hash_array_in++){
                //第一筆比較特別
                if(($count_frame_hash_array_out == 0) && ($count_frame_hash_array_in == 0)){
                    $frame_hash_string = $frame_hash_string.$frame_hash_array[$count_frame_hash_array_out][$count_frame_hash_array_in];
                }else{
                    $frame_hash_string = $frame_hash_string.'-'.$frame_hash_array[$count_frame_hash_array_out][$count_frame_hash_array_in];
                }

                if(($count_frame_hash_array_in == 0)){
                    $frame_blade_hash_string = $frame_blade_hash_string.$frame_hash_array[$count_frame_hash_array_out][$count_frame_hash_array_in];
                }else{
                    $frame_blade_hash_string = $frame_blade_hash_string.'-'.$frame_hash_array[$count_frame_hash_array_out][$count_frame_hash_array_in];
                }
            }
        }

        //如果有毫秒數的話
        // $end_date_time2 = new DateTime(date('Y-m-d H:i:s', ((((int)((string)$end_time))+1200)/1000)));
        // $res2 = DB::collection('switch_site')
        //         ->where('leager_site', $leager_site_id)
        //         ->whereBetween('update_time', array($end_date_time, $end_date_time2))
        //         ->get()[0];
        //如果只有秒數的話
        $res2 = DB::collection('switch_site')
                ->where('leager_site', $leager_site_id)
                ->where('update_time',  $end_date_time)
                ->get()[0];

        $group_iot_item_number = 2;

        $ipfs_video_site_path='';
        $video_path='';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $ipfs_video_site_path = str_replace(array("\\\\", "\\", "//", "/"), '\\',Storage::disk('ipfs_video_site')->getDriver()->getAdapter()->getPathPrefix());
            $video_path = $ipfs_video_site_path.$video_hash;
        }else{
            $ipfs_video_site_path = str_replace(array("\\\\", "\\", "//", "/"), '/',Storage::disk('ipfs_video_site')->getDriver()->getAdapter()->getPathPrefix());
        }

        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $client->post('http://'.$this->django_ip.':8000/video/validation_video/main',[
            'json' => [
            'test_video_hash'=>$video_hash,
            'test_frame_hash_array'=>$frame_hash_string,
            'csrf_name' => csrf_token(),
        ]])->getBody();

        $validation_low_frequency_data = [];
        $validation_low_frequency_data['video_name'] = $video_hash.'.mp4';
        $validation_low_frequency_data['frame_hash_string'] = explode('-',$frame_hash_string);
        $validation_low_frequency_data['img_list'] = $response;
        $validation_low_frequency_data['yolo_blade_tag_string'] = explode('+',$yolo_blade_tag_string);
        $validation_low_frequency_data['frame_blade_hash_string'] = explode('+',$frame_blade_hash_string);
        $validation_low_frequency_data['time_array'] = $time_array;
        
        return $validation_low_frequency_data;
    }

    function validation_background_trace_back_job_table($background_trace_back_job_data){

        //每三個都是一組，中間的是第幾種型態
        //0:null
        //1:字串
        //2:陣列串接(一段)
        //3:陣列串接(二段)

        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $client->post('http://'.$this->django_ip.':8000/check_hmac/check/hmac',[
            'json' => [
            'leager_site_name'=>'leager_site',
            'leager_site_type'=>'1',
            'leager_site_data'=>$background_trace_back_job_data['leager_site'],
            'error_pos_name'=>'error_pos',
            'error_pos_type'=>'1',
            'error_pos_data'=> $background_trace_back_job_data['error_pos'],
            'switch_site_login_id_name'=>'switch_site_login_id',
            'switch_site_login_id_type'=>'1',
            'switch_site_login_id_data'=> $background_trace_back_job_data['switch_site_login_id'],
            'switch_site_logout_id_name'=>'switch_site_logout_id',
            'switch_site_logout_id_type'=>'1',
            'switch_site_logout_id_data'=> $background_trace_back_job_data['switch_site_logout_id'],
            'create_time_name'=>'create_time',
            'create_time_type'=>'1',
            'create_time_data'=>str_replace(array(" "), '---', $background_trace_back_job_data['create_time']),
            'update_time_name'=>'update_time',
            'update_time_type'=>'1',
            'update_time_data'=>str_replace(array(" "), '---', $background_trace_back_job_data['update_time']),
            'csrf_name' => csrf_token(),
            'checksum' =>  $background_trace_back_job_data['checksum'],
        ]])->getBody();

        return $response;
    }

    function validation_trace_asset_in_leagersite_table($trace_asset_in_leagersite_datas){

        $checksum_vaildation_leager_site_id_array = $this->checksum_vaildation_dimation_one_array_create($trace_asset_in_leagersite_datas['leager_site_id_array']);
        
        $checksum_vaildation_switch_site_id_array = $this->checksum_vaildation_dimation_one_array_create($trace_asset_in_leagersite_datas['switch_site_id_array']);

        $checksum_vaildation_error_array = $this->checksum_vaildation_dimation_two_array_create($trace_asset_in_leagersite_datas['error']);

        $checksum_vaildation_error_item_array = $this->checksum_vaildation_dimation_two_array_create($trace_asset_in_leagersite_datas['error_item']);

        $checksum_vaildation_error_time_array = $this->checksum_vaildation_dimation_two_array_create($trace_asset_in_leagersite_datas['error_time']);

        //每三個都是一組，中間的是第幾種型態
        //0:null
        //1:字串
        //2:陣列串接(一段)
        //3:陣列串接(二段)
        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $client->post('http://'.$this->django_ip.':8000/check_hmac/check/hmac',[
            'json' => [
            'asset_RFID_name'=>'asset_RFID',
            'asset_RFID_type'=>'1',
            'asset_RFID_data'=>$trace_asset_in_leagersite_datas['asset_RFID'],
            'leager_site_id_name'=>'leager_site_id_array',
            'leager_site_id_type'=> $checksum_vaildation_leager_site_id_array['array_string_type'],
            'leager_site_id_data'=> $checksum_vaildation_leager_site_id_array['array_string'],
            'switch_site_id_name'=>'switch_site_id_array',
            'switch_site_id_type'=> $checksum_vaildation_switch_site_id_array['array_string_type'],
            'switch_site_id_data'=> $checksum_vaildation_switch_site_id_array['array_string'],
            'error_name'=>'error',
            'error_type'=>$checksum_vaildation_error_array['array_string_type'],
            'error_data'=> $checksum_vaildation_error_array['array_string'],
            'error_item_name'=>'error_item',
            'error_item_type'=>$checksum_vaildation_error_item_array['array_string_type'],
            'error_item_data'=>$checksum_vaildation_error_item_array['array_string'],
            'error_time_name'=>'error_time',
            'error_time_type'=>$checksum_vaildation_error_time_array['array_string_type'],
            'error_time_data'=>$checksum_vaildation_error_time_array['array_string'],
            'create_time_name'=>'create_time',
            'create_time_type'=>'1',
            'create_time_data'=>str_replace(array(" "), '---', $trace_asset_in_leagersite_datas['create_time']),
            'update_time_name'=>'update_time',
            'update_time_type'=>'1',
            'update_time_data'=>str_replace(array(" "), '---', $trace_asset_in_leagersite_datas['update_time']),
            'csrf_name' => csrf_token(),
            'checksum' =>  $trace_asset_in_leagersite_datas['checksum'],
        ]])->getBody();

        return $response;
    }

    function validation_sensor_group_table($sensor_group_datas){
        $checksum_vaildation_asset_RFID_array = $this->checksum_vaildation_dimation_one_array_create($sensor_group_datas['asset_RFID_array']);

        $checksum_vaildation_sensor_ldr_group = $this->checksum_vaildation_dimation_two_array_create($sensor_group_datas['sensor_ldr_group']);

        $checksum_vaildation_sensor_camera_group = $this->checksum_vaildation_dimation_two_array_create($sensor_group_datas['sensor_camera_group']);

        //每三個都是一組，中間的是第幾種型態
        //0:null
        //1:字串
        //2:陣列串接(一段)
        //3:陣列串接(二段)
        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $client->post('http://'.$this->django_ip.':8000/check_hmac/check/hmac',[
            'json' => [
            'leager_site_name'=>'leager_site',
            'leager_site_type'=>'1',
            'leager_site_data'=>$sensor_group_datas['leager_site'],
            'asset_RFID_array_name'=>'asset_RFID_array',
            'asset_RFID_array_type'=> $checksum_vaildation_asset_RFID_array['array_string_type'],
            'asset_RFID_array_data'=> $checksum_vaildation_asset_RFID_array['array_string'],
            'sensor_ldr_group_name'=>'sensor_ldr_group',
            'sensor_ldr_group_type'=> $checksum_vaildation_sensor_ldr_group['array_string_type'],
            'sensor_ldr_group_data'=> $checksum_vaildation_sensor_ldr_group['array_string'],
            'sensor_camera_group_name'=>'sensor_camera_group',
            'sensor_camera_group_type'=>$checksum_vaildation_sensor_camera_group['array_string_type'],
            'sensor_camera_group_data'=> $checksum_vaildation_sensor_camera_group['array_string'],
            'create_time_name'=>'create_time',
            'create_time_type'=>'1',
            'create_time_data'=>str_replace(array(" "), '---', $sensor_group_datas['create_time']),
            'update_time_name'=>'update_time',
            'update_time_type'=>'1',
            'update_time_data'=>str_replace(array(" "), '---', $sensor_group_datas['update_time']),
            'csrf_name' => csrf_token(),
            'checksum' =>  $sensor_group_datas['checksum'],
        ]])->getBody();

        return $response;
    }

    function checksum_vaildation_string_create($vaildation_string){
        if((strcmp($vaildation_string,"")!=0)){
            $data_string = $vaildation_string;
            $data_string_type = "1";
        }else{
            $data_string = "null";
            $data_string_type = "0";
        }
        $return_array=[];
        $return_array['data_string']=$data_string;
        $return_array['data_string_type']=$data_string_type;
        return $return_array;
    }

    function checksum_vaildation_dimation_one_array_create($vaildation_array){
        $array_string = "";
        $array_string_type = "0";
        if(count($vaildation_array) != 0){
            //丟進去python時發現引號會被掉
            //先用*號做每筆資料標記
            //再用-為每筆資料做間隔
            //一個-為時間已經使用的間隔
            //一個--為陣列每筆資料的間隔
            //一個---為陣列每筆資料的間隔中的空白部分
            for($count_array=0;$count_array<count($vaildation_array);$count_array++){
                if($count_array == 0){
                    $array_string = '*'.$vaildation_array[$count_array].'*';
                }else{
                    $array_string = $array_string.'--'.'*'.$vaildation_array[$count_array].'*';
                }
            }
            $array_string_type = "2";
        }else{
            //如果沒有值，為了避免python讀取參數有問題，設定為null
            $array_string = "null";
            $array_string_type = "0";
        }

        $return_array=[];
        $return_array['array_string']=$array_string;
        $return_array['array_string_type']=$array_string_type;
        return $return_array;
    }

    function checksum_vaildation_dimation_two_array_create($vaildation_array){
        $array_string = "";
        $array_string_type = "0";
        if(count($vaildation_array) != 0){
            for($count_dimation_one_array=0;$count_dimation_one_array<count($vaildation_array);$count_dimation_one_array++){
                if($count_dimation_one_array == 0){
                    $array_string = '[';
                }else{
                    $array_string = $array_string.'+';
                }
                for($count_dimation_two_array=0;$count_dimation_two_array<count($vaildation_array[$count_dimation_one_array]);$count_dimation_two_array++){
                    $vaildation_array[$count_dimation_one_array][$count_dimation_two_array] = str_replace(array(" "), '---', $vaildation_array[$count_dimation_one_array][$count_dimation_two_array]);
                    if($count_dimation_two_array == 0){
                        $array_string = $array_string.'*'.$vaildation_array[$count_dimation_one_array][$count_dimation_two_array].'*';
                    }else{
                        $array_string = $array_string.'--'.'*'.$vaildation_array[$count_dimation_one_array][$count_dimation_two_array].'*';
                    }
                }
                $array_string = $array_string.']';
            }
            $array_string_type = '2';
        }else{
            $array_string = "null";
            $array_string_type = '0';
        }

        $return_array=[];
        $return_array['array_string']=$array_string;
        $return_array['array_string_type']=$array_string_type;
        return $return_array;
    }

    function validation_switch_site_table($switch_site_table_data){
        $python = 'python3';
        $checksum_site_path='';
        //checksum_site:app/public/check_hmac
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $checksum_site_path = str_replace(array("\\\\", "\\", "//", "/"), '\\',Storage::disk('checksum_site')->getDriver()->getAdapter()->getPathPrefix());
        }else{
            $checksum_site_path = str_replace(array("\\\\", "\\", "//", "/"), '/',Storage::disk('checksum_site')->getDriver()->getAdapter()->getPathPrefix());
        }

        $checksum_vaildation_camera_id_array = $this->checksum_vaildation_dimation_one_array_create($switch_site_table_data['camera_id_array']);

        $checksum_vaildation_asset_RFID_array = $this->checksum_vaildation_dimation_one_array_create($switch_site_table_data['asset_RFID_array']);

        $checksum_vaildation_ldr_id_array = $this->checksum_vaildation_dimation_one_array_create($switch_site_table_data['ldr_id_array']);

        //IPFS資料處理
        //目前同camera
        $IPFS_string = "";
        $IPFS_string_type = "0";
        if(count($switch_site_table_data['video_IPFS_hash_array']) != 0){
            for($count_video_IPFS_hash=0;$count_video_IPFS_hash<count($switch_site_table_data['video_IPFS_hash_array']);$count_video_IPFS_hash++){
                if($count_video_IPFS_hash == 0){
                    //最後一個2是因為可能再轉入python時，hash長度太特別，去撈到檔案，需要日後再看
                    $IPFS_string = '*'.$switch_site_table_data['video_IPFS_hash_array'][$count_video_IPFS_hash].'2*';
                }else{
                    $IPFS_string = $IPFS_string.'--'.'*'.$switch_site_table_data['video_IPFS_hash_array'][$count_video_IPFS_hash].'2*';
                }
            }
            $IPFS_string_type = "2";
        }else{
            $IPFS_string = "null";
            $IPFS_string_type = "0";
        }

        $checksum_vaildationsensor_ethereum_blockchain_tx_array = $this->checksum_vaildation_dimation_two_array_create($switch_site_table_data['sensor_ethereum_blockchain_tx_array']);


        //每三個都是一組，中間的是第幾種型態
        //0:null
        //1:字串
        //2:陣列串接(一段)
        //3:陣列串接(二段)
        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $client->post('http://'.$this->django_ip.':8000/check_hmac/check/hmac',[
            'json' => [
            'leager_site_name'=>'leager_site',
            'leager_site_type'=>'1',
            'leager_site_data'=>$switch_site_table_data['leager_site'],
            'camera_id_array_name'=>'camera_id_array',
            'camera_id_array_type'=> $checksum_vaildation_camera_id_array['array_string_type'],
            'camera_id_array_data'=> $checksum_vaildation_camera_id_array['array_string'],
            'video_IPFS_hash_array_name'=>'video_IPFS_hash_array',
            'video_IPFS_hash_array_type'=>$IPFS_string_type,
            'video_IPFS_hash_array_data'=> $IPFS_string,
            'sensor_ethereum_blockchain_tx_array_name'=>'sensor_ethereum_blockchain_tx_array',
            'sensor_ethereum_blockchain_tx_array_type'=>$checksum_vaildationsensor_ethereum_blockchain_tx_array['array_string_type'],
            'sensor_ethereum_blockchain_tx_array_data'=>$checksum_vaildationsensor_ethereum_blockchain_tx_array['array_string'],
            'ldr_id_array_name'=>'ldr_id_array',
            'ldr_id_array_type'=>$checksum_vaildation_ldr_id_array['array_string_type'],
            'ldr_id_array_data'=>$checksum_vaildation_ldr_id_array['array_string'],
            'asset_RFID_array_name'=>'asset_RFID_array',
            'asset_RFID_array_type'=>$checksum_vaildation_asset_RFID_array['array_string_type'],
            'asset_RFID_array_data'=>$checksum_vaildation_asset_RFID_array['array_string'],
            'create_time_name'=>'create_time',
            'create_time_type'=>'1',
            'create_time_data'=>str_replace(array(" "), '---', $switch_site_table_data['create_time']),
            'update_time_name'=>'update_time',
            'update_time_type'=>'1',
            'update_time_data'=>str_replace(array(" "), '---', $switch_site_table_data['update_time']),
            'csrf_name' => csrf_token(),
            'checksum' =>  $switch_site_table_data['checksum'],
        ]])->getBody();
        
        // print_r($switch_site_table_validation_json);
        // return $switch_site_table_validation_json;
        return $response;
     }

     function check_trace_asset_in_leagersite($asset_ID, $trace_branch, $recode_cur_switch_site_pos){
         
        $trace_asset_in_leagersite_data = DB::collection('trace_asset_in_leagersite')
                                                ->where('asset_RFID', $asset_ID)
                                                ->get()[0];
        //$trace_branch:0是主流，1是分支
        //主流:只檢查第一項
        //分支:需要每一項檢查
        print_r('check_trace_asset_in_leagersite1+');
        print_r($trace_branch);
        print_r($asset_ID);
        print_r($recode_cur_switch_site_pos);
        if($trace_branch == 0){
            if(strcmp($trace_asset_in_leagersite_data['error'][$recode_cur_switch_site_pos][count($trace_asset_in_leagersite_data['error'][$recode_cur_switch_site_pos])-1],'ok') == 0){
                print_r('check_trace_asset_in_leagersite2+');
                return 0;
            }elseif(
                (strcmp($trace_asset_in_leagersite_data['error'][$recode_cur_switch_site_pos][count($trace_asset_in_leagersite_data['error'][$recode_cur_switch_site_pos])-1],'error') == 0) && 
                (strcmp($trace_asset_in_leagersite_data['error'][$recode_cur_switch_site_pos][count($trace_asset_in_leagersite_data['error'][$recode_cur_switch_site_pos])-2],'error') == 0) &&
                (strcmp($trace_asset_in_leagersite_data['error'][$recode_cur_switch_site_pos][count($trace_asset_in_leagersite_data['error'][$recode_cur_switch_site_pos])-3],'error') == 0)
            ){
                print_r('check_trace_asset_in_leagersite3+');
                return 1; 
            }else{
                //其他狀況，目前包括未驗證
                print_r('check_trace_asset_in_leagersite4+');
                return 2;
            }
        }elseif($trace_branch == 1){
            for($count_table_error_array=0;$count_table_error_array<count($trace_asset_in_leagersite_data['error']);$count_table_error_array++){
                //假設第一種狀況完成
                if(strcmp($trace_asset_in_leagersite_data['error'][$count_table_error_array][count($trace_asset_in_leagersite_data['error'][$count_table_error_array])-1],'ok') == 0){
                    print_r('check_trace_asset_in_leagersite5+');
                    return 0;
                }elseif(
                    (strcmp($trace_asset_in_leagersite_data['error'][$count_table_error_array][count($trace_asset_in_leagersite_data['error'][$count_table_error_array])-1],'error') == 0) && 
                    (strcmp($trace_asset_in_leagersite_data['error'][$count_table_error_array][count($trace_asset_in_leagersite_data['error'][$count_table_error_array])-2],'error') == 0) &&
                    (strcmp($trace_asset_in_leagersite_data['error'][$count_table_error_array][count($trace_asset_in_leagersite_data['error'][$count_table_error_array])-3],'error') == 0)
                ){
                    print_r('check_trace_asset_in_leagersite6+');
                    return 1; 
                }else{
                    //其他狀況，目前包括未驗證
                    print_r('check_trace_asset_in_leagersite7+');
                    return 2;
                }
            }
        }
     }

     function update_trace_asset_in_leagersite($determine_error,$recode_cur_switch_site_pos,$asset_id, $error_message){

        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $client->post('http://'.$this->django_ip.':8000/check_hmac/process_trace_asset_in_leagersite_table/update_trace_asset_in_leagersite',[
            'json' => [
            'determine_error'=>$determine_error,
            'recode_cur_switch_site_pos_array'=>$recode_cur_switch_site_pos,
            'asset_id_array'=>$asset_id,
            'error_message'=>$error_message,
            'csrf_name' => csrf_token(),
        ]])->getBody();

        return $response;
     }
}

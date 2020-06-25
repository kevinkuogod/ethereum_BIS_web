<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\TraceController;
use DB;
use Illuminate\Support\Facades\Storage;

class Testjob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $Datas;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($get_data)
    // public function __construct()
    {
        //
        $this->Datas = $get_data;
        print_r($this->Datas['book_id']);
        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $TraceController = new TraceController; 
        // 測試要去mongoDB相對位置查看
        // print_r($this->Datas['book_id'].'2');
        // DB::collection('trace_asset_in_leagersite')
        //             ->where('asset_RFID', 'Z001')
        //             ->push('error.0', '真理大學好棒棒');
        //mongoDB的insert需要先有一筆資料再用Push添加array，沒model的話
        // DB::collection('test_redis')->insert(['實驗室測試' =>'真理大學']);
        // DB::collection('test_redis')->where('實驗室測試', '真理大學')->update(['error'=>[]]);


        $file = fopen("/var/www/html/traceability_project/storage/logs/validation_error.txt","a+"); //開啟檔案

        //取得待驗證的第一筆資料
        $background_trace_back_job_datas = DB::collection('background_trace_back_job')->get()[0];

        //驗證background_trace_back_job資料表
        $background_trace_back_job_datas_result = $TraceController->validation_background_trace_back_job_table($background_trace_back_job_datas);
        $background_trace_back_job_datas_result_decode = json_decode($background_trace_back_job_datas_result, true);

        if((strcmp($background_trace_back_job_datas_result_decode['error'],'ok')==0)){
            //用待驗證的資料中的id找尋進場資料表中對應的資料
            $switch_site_login_data = DB::collection('switch_site')
                                                ->where('_id', $background_trace_back_job_datas['switch_site_login_id'])
                                                ->get()[0];
            //確保進廠資料表沒有被串改
            $switch_site_login_data_validation_result = $TraceController->validation_switch_site_table($switch_site_login_data);
            //驗證解析
            $switch_site_login_data_validation_result_decode = json_decode($switch_site_login_data_validation_result, true);

            if((strcmp($switch_site_login_data_validation_result_decode['error'],'ok')==0)){
                //用待驗證的資料中的id找尋出場資料表中對應的資料
                $switch_site_logout_data = DB::collection('switch_site')
                                                ->where('_id', $background_trace_back_job_datas['switch_site_logout_id'])
                                                ->get()[0];
 
                //確保出廠資料表沒有被串改
                $switch_site_logout_data_validation_result = $TraceController->validation_switch_site_table($switch_site_logout_data);
                $switch_site_logout_data_validation_result_decode = json_decode($switch_site_logout_data_validation_result, true);

                if((strcmp($switch_site_logout_data_validation_result_decode['error'],'ok')==0)){
                    //取asset_id
                    $trace_asset_in_leagersite_status = 1;
                    //判定能否刪除背景驗證
                    $mixing_validation_status = 0;
                    $asset_RFID_string_tmp="";
                    $recode_cur_switch_site_pos_tmp="";
                    for($count_asset_RFID_array=0;$count_asset_RFID_array<count($switch_site_logout_data['asset_RFID_array']);$count_asset_RFID_array++){
                        // print($switch_site_logout_data['asset_RFID_array'][$count_asset_RFID_array]);
                        // print($count_asset_RFID_array);
                        // print('---------------------------------------------------------------------------------------------------');
                        print_r($switch_site_logout_data['asset_RFID_array']);
                        $validation_hight_frequency_data_rusult = $TraceController->validation_hight_frequency_data($switch_site_logout_data['leager_site'], $switch_site_logout_data['asset_RFID_array'][$count_asset_RFID_array], $switch_site_logout_data['ldr_id_array'][$count_asset_RFID_array], $switch_site_logout_data['camera_id_array'][$count_asset_RFID_array], $switch_site_login_data['create_time'], $switch_site_logout_data['create_time'],$count_asset_RFID_array);
                        $validation_low_frequency_data_rusult = $TraceController->validation_low_frequency_data($switch_site_logout_data['leager_site'], $switch_site_logout_data['asset_RFID_array'][$count_asset_RFID_array], $switch_site_logout_data['ldr_id_array'][$count_asset_RFID_array], $switch_site_logout_data['camera_id_array'][$count_asset_RFID_array], $switch_site_login_data['create_time'], $switch_site_logout_data['create_time'], $switch_site_logout_data['video_IPFS_hash_array'][$count_asset_RFID_array],$count_asset_RFID_array);
                        $validation_hight_frequency_data_rusult_decode = json_decode($validation_hight_frequency_data_rusult['img_list'], true);
                        $validation_low_frequency_data_rusult_decode = json_decode($validation_low_frequency_data_rusult['img_list'], true);
                        $trace_asset_in_leagersite_data = DB::collection('trace_asset_in_leagersite')
                                                                ->where('asset_RFID', $switch_site_logout_data['asset_RFID_array'][$count_asset_RFID_array])
                                                                ->get()[0];
                        //需要在這邊多家這一道判斷，在error_message
                        $trace_asset_in_leagersite_datas_jaon = $TraceController->validation_trace_asset_in_leagersite_table($trace_asset_in_leagersite_data);
                        $trace_asset_in_leagersite_datas_jaon_decode = json_decode($trace_asset_in_leagersite_datas_jaon, true);
                        
                        $recode_cur_switch_site_pos = 0;
                        for($count_asset_switch_site_array=0;$count_asset_switch_site_array<count($trace_asset_in_leagersite_data['leager_site_id_array']);$count_asset_switch_site_array++){
                            if(strcmp($trace_asset_in_leagersite_data['leager_site_id_array'][$count_asset_switch_site_array],$switch_site_logout_data['leager_site']) == 0){
                                $recode_cur_switch_site_pos = $count_asset_switch_site_array;
                            }
                        }
                        //為了完成頁面快速連結，讓分支都能導向主流
                        if($count_asset_RFID_array==0){
                            $asset_RFID_string_tmp = $asset_RFID_string_tmp.$switch_site_logout_data['asset_RFID_array'][$count_asset_RFID_array];
                            $recode_cur_switch_site_pos_tmp = $recode_cur_switch_site_pos_tmp.(string)$recode_cur_switch_site_pos;
                        }
                        //isset($validation_hight_frequency_data_rusult_decode['error_message']) 會發現找不到變數，所以為error_message
                        if( (isset($validation_hight_frequency_data_rusult_decode['error_message'])) && (isset($validation_low_frequency_data_rusult_decode['error_message']))  && (strcmp($trace_asset_in_leagersite_datas_jaon_decode['error'],'ok')==0)){
                            $mixing_validation_status = 1;
                            $update_trace_asset_in_leagersite_json = $TraceController->update_trace_asset_in_leagersite('ok', $recode_cur_switch_site_pos_tmp, $asset_RFID_string_tmp, "null");
                            // print('hahahahaha validation ok');
                            // print('---------------------------------------------------------------------------------------------------');
                        }else{
                            //一次驗證失敗就離開
                            if($count_asset_RFID_array!=0){
                                $asset_RFID_string_tmp = $asset_RFID_string_tmp.'--'.$switch_site_logout_data['asset_RFID_array'][$count_asset_RFID_array];
                                $recode_cur_switch_site_pos_tmp = $recode_cur_switch_site_pos_tmp.'--'.(string)$recode_cur_switch_site_pos;
                            }
                            $mixing_validation_status = 0;
                            if((!isset($validation_hight_frequency_data_rusult_decode['error_message']))){
                                // print_r("笑笑就好1");
                                // print_r($validation_hight_frequency_data_rusult_decode);
                                $update_trace_asset_in_leagersite_json = $TraceController->update_trace_asset_in_leagersite('error', $recode_cur_switch_site_pos_tmp, $asset_RFID_string_tmp, $validation_hight_frequency_data_rusult_decode['error_message']);
                                fwrite($file,"hight_frequency_data_rusult error\n");
                                fwrite($file,"error_message:".$validation_hight_frequency_data_rusult_decode['error_message']."\n");
                                // print('---------------------------------------------------------------------------------------------------');
                            }elseif((!isset($validation_low_frequency_data_rusult_decode['error_message']))){
                                // print_r("笑笑就好2");
                                // print_r($validation_low_frequency_data_rusult_decode);
                                $update_trace_asset_in_leagersite_json = $TraceController->update_trace_asset_in_leagersite('error', $recode_cur_switch_site_pos_tmp, $asset_RFID_string_tmp, $validation_low_frequency_data_rusult_decode['error_message']);
                                fwrite($file,"low_frequency_data_rusult error\n");
                                fwrite($file,"error_message:".$validation_low_frequency_data_rusult_decode['error_message']."\n");
                                // print('---------------------------------------------------------------------------------------------------');
                            }elseif((strcmp($trace_asset_in_leagersite_datas_jaon_decode['error'],'ok')!=0)){
                                // print_r("笑笑就好3");
                                $update_trace_asset_in_leagersite_json = $TraceController->update_trace_asset_in_leagersite('error', $recode_cur_switch_site_pos_tmp, $asset_RFID_string_tmp, $trace_asset_in_leagersite_datas_jaon_decode['error']);
                                fwrite($file,"trace_asset_in_leagersite 資料表被串改\n");
                                fwrite($file,"error_message:".$trace_asset_in_leagersite_datas_jaon_decode['error']."\n");
                                // print('---------------------------------------------------------------------------------------------------');
                            }
                            // DB::collection('test_redis')->where('實驗室測試', '真理大學')->push('error', '真理大學好棒棒05');
                            // if($count_asset_RFID_array == 0){
                            //     $trace_asset_in_leagersite_status = $TraceController->check_trace_asset_in_leagersite($switch_site_logout_data['asset_RFID_array'][$count_asset_RFID_array], 1, $recode_cur_switch_site_pos);
                            // }else{
                            //     $trace_asset_in_leagersite_status = $TraceController->check_trace_asset_in_leagersite($switch_site_logout_data['asset_RFID_array'][$count_asset_RFID_array], 0, $recode_cur_switch_site_pos);
                            // }
                            // DB::collection('test_redis')->where('實驗室測試', '真理大學')->push('error', '真理大學好棒棒05');
                            // if(($trace_asset_in_leagersite_status == 0) || ($trace_asset_in_leagersite_status == 1)){
                            //     //失敗太多次不繼續執行
                            //     DB::collection('background_trace_back_job')
                            //             ->where('_id', $background_trace_back_job_datas_result['_id'])
                            //             ->delete();
                            //     break;
                            // }elseif($trace_asset_in_leagersite_status == 2){
                            // }
                        }
                    }

                    //最後會寫完檢查用的
                    DB::collection('test_redis')->where('實驗室測試', '真理大學')->push('error', $mixing_validation_status);
                    if($mixing_validation_status == 1){
                        DB::collection('background_trace_back_job')
                            ->where('_id', $background_trace_back_job_datas['_id'])
                            ->delete();
                    }
                    fclose($file);
                }else{
                    //switch_site error
                    print("switch_site (logout) 資料表被串改");
                    fwrite($file,"switch_site (logout) 資料表被串改\n");
                    // fwrite($file,'leager_site:'.$switch_site_logout_data['leager_site']."\n");
                    // $camera_id_string = $this->make_array_to_string($switch_site_logout_data['camera_id_array']);
                    // fwrite($file,'camera_id_array:'.$camera_id_string."\n");
                    // $video_IPFS_hash_string = $this->make_array_to_string($switch_site_logout_data['video_IPFS_hash_array']);
                    // fwrite($file,'video_IPFS_hash_array:'.$video_IPFS_hash_string."\n");
                    // $sensor_ethereum_blockchain_tx_string = $this->make_array_to_string($switch_site_logout_data['sensor_ethereum_blockchain_tx_array']);
                    // fwrite($file,'sensor_ethereum_blockchain_tx_array:'.$sensor_ethereum_blockchain_tx_string."\n");
                    // $ldr_id_string = $this->make_array_to_string($switch_site_logout_data['ldr_id_array']);
                    // fwrite($file,'ldr_id_array:'.$ldr_id_string."\n");
                    // $asset_RFID_string = $this->make_array_to_string($switch_site_logout_data['asset_RFID_array']);
                    // fwrite($file,'asset_RFID_array:'.$asset_RFID_string."\n");
                    // fwrite($file,'create_time:'.(string)$switch_site_logout_data['create_time']."\n");
                    // fwrite($file,'update_time:'.(string)$switch_site_logout_data['update_time']."\n");
                    // fwrite($file,'checksum:'.$switch_site_logout_data['checksum']."\n");
                    // fwrite($file,'validation_time:'.(string)date("Y-m-d H-i-s")."\n");
                    fclose($file);

                    // DB::collection('switch_site')
                    //     ->where('_id', $switch_site_login_data['_id'])
                    //     ->delete();
                }
            }else{
                //switch_site error
                print("switch_site (login) 資料表被串改");
                fwrite($file,"switch_site (login) 資料表被串改"."\n");
                // fwrite($file,'leager_site:'.$switch_site_login_data['leager_site']."\n");
                // $camera_id_string = $this->make_array_to_string($switch_site_login_data['camera_id_array']);
                // fwrite($file,'camera_id_array:'.$camera_id_string."\n");
                // $video_IPFS_hash_string = $this->make_array_to_string($switch_site_login_data['video_IPFS_hash_array']);
                // fwrite($file,'video_IPFS_hash_array:'.$video_IPFS_hash_string."\n");
                // $sensor_ethereum_blockchain_tx_string = $this->make_array_to_string($switch_site_login_data['sensor_ethereum_blockchain_tx_array']);
                // fwrite($file,'sensor_ethereum_blockchain_tx_array:'.$sensor_ethereum_blockchain_tx_string."\n");
                // $ldr_id_string = $this->make_array_to_string($switch_site_login_data['ldr_id_array']);
                // fwrite($file,'ldr_id_array:'.$ldr_id_array."\n");
                // $asset_RFID_string = $this->make_array_to_string($switch_site_login_data['asset_RFID_array']);
                // fwrite($file,'asset_RFID_array:'.$asset_RFID_string."\n");
                // fwrite($file,'create_time:'.$switch_site_login_data['create_time']."\n");
                // fwrite($file,'update_time:'.(string)$switch_site_login_data['update_time']."\n");
                // fwrite($file,'checksum:'.$switch_site_login_data['checksum']."\n");
                // fwrite($file,'validation_time:'.(string)date("Y-m-d H-i-s")."\n");
                fclose($file);

                // DB::collection('switch_site')
                //     ->where('_id', $switch_site_login_data['_id'])
                //     ->delete();
            }
        }else{
            //validation_background_trace_back_job_table error
            print("background_trace_back_job_table 資料表被串改");
            fwrite($file,"background_trace_back_job_table 資料表被串改"."\n");
            // fwrite($file,'leager_site:'.$background_trace_back_job_datas['leager_site']."\n");
            // $error_pos = $this->make_array_to_string($background_trace_back_job_datas['error_pos']);
            // fwrite($file,'error_pos:'.$error_pos."\n");
            // fwrite($file,'switch_site_login_id:'.$background_trace_back_job_datas['switch_site_login_id']."\n");
            // fwrite($file,'switch_site_logout_id:'.$background_trace_back_job_datas['switch_site_logout_id']."\n");
            // fwrite($file,'create_time:'.(string)$background_trace_back_job_datas['create_time']."\n");
            // fwrite($file,'update_time:'.(string)$background_trace_back_job_datas['update_time']."\n");
            // fwrite($file,'checksum:'.$background_trace_back_job_datas['checksum']."\n");
            // fwrite($file,'validation_time:'.(string)date("Y-m-d H-i-s")."\n");
            fclose($file);

            // DB::collection('background_trace_back_job')
            //         ->where('_id', $background_trace_back_job_datas['_id'])
            //         ->delete();
        } 
    }

    function make_array_to_string($ori_array){
        $string='[';
        if (strcmp(gettype($ori_array),"array") == 0){
            foreach($ori_array as $data){
                if (strcmp(gettype($data),"array") == 0){
                    $string = $string.'[';
                    $string = $string.$this->make_array_to_string($data);
                    $string = $string.']';
                }else{
                    $string = $string.(string)$data;
                }
            }
        }else{
            $string = $string.','.(string)$ori_array;
        }
        $string = $string.']';
        return $string;
    }
}

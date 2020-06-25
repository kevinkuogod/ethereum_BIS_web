<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>測試追朔</title>
    </head>
    <body>
        <div style="color: #A830FF;border:2px #ccc solid;padding:1% 30% 1% 35%;margin:0% 0% 1% 0%;">追朔系統(供應鏈階段低頻率資料查詢頁面)</div>
        <div id="system_error_message"  style="color:#880000;border:2px #ccc solid;padding:10px;;margin:0% 0% 1% 0%;"></div>
        <div>
            @if(isset($video_IPFS_hash))
                <button onclick="check_video('{{ $video_IPFS_hash }}')">前往影片查詢</button>
            @endif

            @if(isset($error_message))
                {{ $error_message }}
            @endif
            <a href="{{ $video_path }}">下載檔案</a>
            <video width="320" height="240" controls>
                <source src="{{ $video_path }}" type="video/mp4">
                 Your browser does not support the video tag.
            </video>

            <!-- <video id="trace_back_video" width="320" height="240" controls>
                <source src="" type="video/mp4">
            </video> -->

            <table class="table" id="validation_table" border="1">
                <thead>
                    <tr>
                        <td class="col-md-1">id</td>
                        <td class="col-md-1">yolo_tag</td>
                        <td class="col-md-1">FRAME_hash</td>
                        <td class="col-md-2">IPFS_hash</td>
                        <td class="col-md-2">時間戳</td>
                        <td class="col-md-2">驗證資訊</td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </body>
    
    <script>
        let system_error_message_obj = document.getElementById('system_error_message');

        @if(isset($video_path) && isset($trace_item_detail_array))
            let validation_table_obj = document.getElementById('validation_table');
            let Tr,a_obj,button_obj,select_obj;
            @for ($i = 0; $i < count($frame_hash_string); $i++)
                Tr = validation_table_obj.insertRow(validation_table_obj.rows.length);
                css_text="";
                @if(isset($trace_item_detail_array[$i]))
                    if('{{ $trace_item_detail_array[$i] }}' == 'ok'){
                        // console.log('ok');
                        css_text='background-color:#00DD00';
                    }else{
                        // console.log('error');
                        system_error_message_obj.innerText = "{{ $trace_item_detail_array[$i] }}";
                        css_text='background-color:#880000';
                    }
                @endif

                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = ({{ $i }}+1).toString();
                    Td.style.cssText = css_text;

                @if(isset($yolo_tag_string[$i]))
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "{{ $yolo_tag_string[$i] }}";
                    Td.style.cssText = css_text;
                @else
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "";
                    Td.style.cssText = css_text;
                @endif


                @if(isset($frame_hash_string[$i]))
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "{{ $frame_hash_string[$i] }}";
                    Td.style.cssText = css_text;
                @else
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "";
                    Td.style.cssText = css_text;
                @endif

                @if(isset($IPFS_frame_hash_array[$i]))
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "{{ $IPFS_frame_hash_array[$i] }}";
                    Td.style.cssText = css_text;
                @else
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "";
                    Td.style.cssText = css_text;
                @endif

                @if(isset($time_array[$i]))
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "{{ $time_array[$i] }}";
                    Td.style.cssText = css_text;
                @else
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "";
                    Td.style.cssText = css_text;
                @endif

                @if(isset($trace_item_detail_array[$i]))
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "{{ $trace_item_detail_array[$i] }}";
                    Td.style.cssText = css_text;
                @else
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "";
                    Td.style.cssText = css_text;
                @endif
            @endfor
            if(system_error_message_obj.innerText != ""){
                system_error_message_obj.innerText = system_error_message_obj.innerText+"請重新驗證此供應鏈階段";
            }
        @else
            system_error_message_obj.innerText = "{{ $error_message }}";
        @endif

    </script>
</html>

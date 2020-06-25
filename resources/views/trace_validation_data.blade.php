<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>測試追朔</title>
    </head>
    <body>
        <div style="color: #A830FF;border:2px #ccc solid;padding:1% 30% 1% 35%;margin:0% 0% 1% 0%;">追朔系統(供應鏈階段高頻率資料查詢頁面)</div>
        <div id="system_error_message"  style="color:#880000;border:2px #ccc solid;padding:10px;;margin:0% 0% 1% 0%;"></div>
        <div>
            @if(isset($video_IPFS_hash))
                <button onclick="check_video('{{ $video_IPFS_hash }}')">前往影片查詢</button>
            @endif
            @if(isset($error_message))
                {{ $error_message }}
            @endif
            <table class="table" id="validation_table" border="1">
                <thead>
                    <tr>
                        <td class="col-md-1">第幾筆資料</td>
                        <td class="col-md-2">光敏電阻資料</td>
                        <td class="col-md-2">時間戳</td>
                        <td class="col-md-2">驗證訊息</td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <div id="hide_from"></div>
            </table>
        </div>
    </body>
    
    <script>
        let system_error_message_obj = document.getElementById('system_error_message');
        
        @if(isset($ldr_data_array) && isset($yolo_tag_array) && isset($video_IPFS_hash) && isset($trace_item_detail_array))
            let validation_table_obj = document.getElementById('validation_table');
            let Tr,a_obj,button_obj,select_obj;
            @for ($i = 0; $i < count($ldr_data_array); $i++)
                Tr = validation_table_obj.insertRow(validation_table_obj.rows.length);
                css_text="";
                if('{{ $trace_item_detail_array[$i] }}' == 'ok'){
                    // console.log('ok');
                    css_text='background-color:#00DD00';
                }else{
                    // console.log('error');
                    css_text='background-color:#880000';
                    system_error_message_obj.innerText = "{{ $trace_item_detail_array[$i] }}";
                }

                Td = Tr.insertCell(Tr.cells.length);
                Td.innerText = ({{ $i }}+1).toString();
                Td.style.cssText = css_text;

                Td = Tr.insertCell(Tr.cells.length);
                Td.innerText = "{{ $ldr_data_array[$i] }}";
                Td.style.cssText = css_text;

                Td = Tr.insertCell(Tr.cells.length);
                Td.innerText = "{{ $time_array[$i] }}";
                Td.style.cssText = css_text;

                Td = Tr.insertCell(Tr.cells.length);
                Td.innerText = "{{ $trace_item_detail_array[$i] }}";
                Td.style.cssText = css_text;
            @endfor
            if(system_error_message_obj.innerText != ""){
                system_error_message_obj.innerText = system_error_message_obj.innerText+" 請重新驗證此供應鏈階段";
            }
        @else
            system_error_message_obj.innerText = "{{ $error_message }}";
        @endif

        function check_video(check_video_hash){
            if(system_error_message_obj.innerText == ""){
                let form_obj = document.createElement("form");
                form_obj.setAttribute("action","{{ route('trace.get_video_data') }}");
                form_obj.setAttribute("method","post");
                let input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "leager_site_id";
                input_obj.value = "{{ $leager_site_id }}";
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "asset_ID";
                input_obj.value = "{{ $asset_ID }}";
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "camera_ID";
                input_obj.value = "{{ $camera_ID }}";
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "start_time";
                input_obj.value = "{{ $start_time }}";
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "end_time";
                input_obj.value = "{{ $end_time }}";
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "sensor_group_number";
                input_obj.value = "{{ $sensor_group_number }}";
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "video_IPFS_hash";
                input_obj.value = check_video_hash;
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "hidden";
                input_obj.name = "_token";
                input_obj.value = '{{ csrf_token() }}';
                form_obj.appendChild(input_obj);
                input_obj = document.createElement("input");
                input_obj.type = "submit";
                form_obj.setAttribute("id","submit_input");
                form_obj.appendChild(input_obj);
                let hide_from_obj = document.getElementById('hide_from');
                hide_from_obj.appendChild(form_obj);
                let submit_input_obj = document.getElementById('submit_input');
                submit_input_obj.submit();
            }else{
                alert(system_error_message_obj.innerText);
            }
        }
    </script>
</html>

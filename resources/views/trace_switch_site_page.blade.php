<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>測試追朔</title>
    </head>
    <body>
        <div style="color: #A830FF;border:2px #ccc solid;padding:1% 40% 1% 30%;margin:0% 0% 1% 0%;">追朔系統(供應鏈階段進出場查詢頁面)</div>
        <div id="system_error_message"  style="color:#880000;border:2px #ccc solid;padding:10px;;margin:0% 0% 1% 0%;"></div>
        <div>
            <table class="table"  id="switch_table" border="1">
                <thead>
                    <tr>
                        <!-- <td class="col-md-1"><input onclick="checked_allPage();" type="checkbox" id="page_id_all" ></td> -->
                        <td class="col-md-1">請選擇RFID代號</td>
                        <td class="col-md-1">請選擇光感測器代號</td>
                        <td class="col-md-1">請選擇攝影機代號</td>
                        <td class="col-md-2">入場時間</td>
                        <td class="col-md-2">出場時間</td>
                        <td class="col-md-2">查詢</td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="hide_from"></div>
        </div>
    </body>

    <script>
        @if(isset($switch_site_table_datas))
            let switch_table_obj = document.getElementById('switch_table');
            let Tr,a_obj,button_obj,select_obj;
            count_select = 0;
            @for ($count_switch_site_table_datas = 0; $count_switch_site_table_datas < count($switch_site_table_datas); $count_switch_site_table_datas++)
                @for ($count_asset_RFID_array = 0; $count_asset_RFID_array < count($switch_site_table_datas[$count_switch_site_table_datas]); $count_asset_RFID_array++)
                    Tr = switch_table_obj.insertRow(switch_table_obj.rows.length);
                
                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['asset_RFID'] }}";

                    Td = Tr.insertCell(Tr.cells.length);
                    select_obj = document.createElement("select");
                    select_obj.id='ldr'+ count_select;
                    select_obj.setAttribute("onchange","set_select_position('ldr',"+count_select+")");
                    @for ($i_ldr_obj = 0; $i_ldr_obj < count($switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['ldr_id_array']); $i_ldr_obj++)
                        select_obj.options[select_obj.length] = new Option("{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['ldr_id_array'][$i_ldr_obj] }}", "{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['ldr_id_array'][$i_ldr_obj] }}");
                    @endfor
                    Td.appendChild(select_obj);

                    Td = Tr.insertCell(Tr.cells.length);
                    select_obj = document.createElement("select");
                    select_obj.id='camera'+ count_select;
                    select_obj.setAttribute("onchange","set_select_position('camera',"+count_select+")");
                    @for ($i_camera_obj = 0; $i_camera_obj < count($switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['camera_id_array']); $i_camera_obj++)
                        select_obj.options[select_obj.length] = new Option("{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['camera_id_array'][$i_camera_obj] }}", "{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['camera_id_array'][$i_camera_obj] }}");
                    @endfor
                    Td.appendChild(select_obj);

                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "{{$switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['start_time']}}";

                    Td = Tr.insertCell(Tr.cells.length);
                    Td.innerText = "{{$switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['end_time']}}";

                    Td = Tr.insertCell(Tr.cells.length);
                    button_obj = document.createElement("button");
                    button_obj.setAttribute("type","button");
                    button_obj.setAttribute("onclick",'go_validation_data_page("'+"{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['leager_site'] }}"+'","'+"{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['asset_RFID'] }}"+'","'+'camera'+count_select+'","'+'ldr'+count_select+'","'+"{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['start_time'] }}"+'","'+"{{ $switch_site_table_datas[$count_switch_site_table_datas][$count_asset_RFID_array]['end_time'] }}"+'","'+"{{ $count_asset_RFID_array }}"+'")');
                    button_obj.innerText="查詢場景資料";
                    Td.appendChild(button_obj);
                    count_select++;
                @endfor
            @endfor
        @else
        @endif

        function set_select_position(set_obj,set_position){

            let select_ldr_id = 'ldr'+set_position;
            let select_camera_id = 'camera'+set_position;
            
            let select_ldr_obj = document.getElementById(select_ldr_id);
            let select_camera_obj = document.getElementById(select_camera_id);

            if(set_obj == 'ldr'){
                select_camera_obj.selectedIndex = select_ldr_obj.selectedIndex;
            }else if(set_obj == 'camera'){
                select_ldr_obj.selectedIndex = select_camera_obj.selectedIndex;
            }
        }

        function go_validation_data_page(leager_site_id, asset_ID, camera_ID, ldr_ID, start_time, end_time, sensor_group_number){
            let system_error_message_obj = document.getElementById('system_error_message');
            if(system_error_message_obj.innerText == ""){
                console.log(leager_site_id);
                console.log(asset_ID);
                console.log(camera_ID);
                console.log(ldr_ID);
                console.log(start_time);
                console.log(end_time);
                console.log(sensor_group_number);

                let camera_ID_obj = document.getElementById(camera_ID);
                let ldr_ID_obj = document.getElementById(ldr_ID);
                console.log(camera_ID_obj);
                console.log(ldr_ID_obj);

                let form_obj = document.createElement("form");
                form_obj.setAttribute("action","{{ route('trace.get_validation_data') }}");
                form_obj.setAttribute("method","post");
                let input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "leager_site_id";
                input_obj.value = leager_site_id;
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "asset_ID";
                input_obj.value = asset_ID;
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "camera_ID";
                input_obj.value = camera_ID_obj.options[camera_ID_obj.selectedIndex].text;
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "ldr_ID";
                input_obj.value = ldr_ID_obj.options[ldr_ID_obj.selectedIndex].text;
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "sensor_group_number";
                input_obj.value = sensor_group_number;
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "start_time";
                input_obj.value = start_time;
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "end_time";
                input_obj.value = end_time;
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

        @if(isset($error_str))
            let system_error_message_obj = document.getElementById('system_error_message');
            system_error_message_obj.innerText= "{{ $error_str }}".replace(/text_return/g,"\n");
        @endif
    </script>
</html>

import pymongo
import datetime
import hmac
import hashlib
import time

client = pymongo.MongoClient("localhost",27017)
db = client.iotsensordata
trace_asset_in_leagersite = db.trace_asset_in_leagersite
login_time = time.strftime("%Y-%m-%d %H:%M:%S")
def _generate_signature(data):
  return hmac.new('kevin'.encode('utf-8'), data.encode('utf-8'), hashlib.sha256).hexdigest()

#創建預設產品供應鏈階段的資產
asset_RFID=['tea1','Desiccant1','wrapper1']
#都為產品供應鏈階段id
search_data_list=[[ '5f227bcffadc97903dfca903',
                    '5f227bcffadc97903dfca906',
                    '5f227bcffadc97903dfca907',
                    '5f227bcffadc97903dfca908',
                    '5f227bcffadc97903dfca909'
                   ],
                   [
                     '5f227bcffadc97903dfca904',
                     '5f227bcffadc97903dfca906'
                   ],
                   [
                     '5f227bcffadc97903dfca905',
                     '5f227bcffadc97903dfca906'
                   ]
                  ]

switch_site_id_array=[[],[],[]]

error=[
        [['wait'],['wait'],['wait'],['wait'],['wait']],
        [['wait'], ['wait']],
        [['wait'], ['wait']]
      ]

error_time=[
            [[login_time],[login_time],[login_time],[login_time],[login_time]],
            [[login_time],[login_time]],
            [[login_time],[login_time]]
           ]

error_item=[
            [[],[],[],[],[]],
            [[],[]],
            [[],[]]
          ]

for count_asset_RFID in range(len(asset_RFID)):
  checksum_data = ""
  checksum_data = checksum_data+'asset_RFID'+asset_RFID[count_asset_RFID]
  checksum_data = checksum_data+'leager_site_id_array'+str(search_data_list[count_asset_RFID])
  checksum_data = checksum_data+'switch_site_id_array'+str(switch_site_id_array[count_asset_RFID])
  checksum_data = checksum_data+'error'+str(error[count_asset_RFID])
  checksum_data = checksum_data+'error_item'+str(error_item[count_asset_RFID])
  checksum_data = checksum_data+'error_time'+str(error_time[count_asset_RFID])
  checksum_data = checksum_data+'create_time'+str(login_time)
  checksum_data = checksum_data+'update_time'+str(login_time)
  checksum_data = _generate_signature(checksum_data)

  realtime_data_id = trace_asset_in_leagersite.insert_one({'asset_RFID':asset_RFID[count_asset_RFID],\
                                                         'leager_site_id_array':search_data_list[count_asset_RFID],\
                                                         'switch_site_id_array':switch_site_id_array[count_asset_RFID],\
                                                         'error':error[count_asset_RFID],\
                                                         'error_item':error_item[count_asset_RFID],\
                                                         'error_time':error_time[count_asset_RFID],\
                                                         'create_time':login_time,\
                                                         'update_time':login_time,\
                                                         'checksum':checksum_data
                                                        })
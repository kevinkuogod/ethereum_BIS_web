import pymongo
from bson.objectid import ObjectId
client = pymongo.MongoClient("localhost",27017)
db = client.iotsensordata

realtime_sensor_data = db.realtime_sensor_data
search_data_condition={"_id":ObjectId('5eddca38262a3eb07ddaf034')}
search_data_list=realtime_sensor_data.find(search_data_condition)[0]

# #更改值
# update_parameter={"$set":{"asset_RFID":'tea1'}}
# # update_parameter={"$set":{"asset_RFID":'Desiccant1'}}

# #更改值
# ldr_data_array = []
# for count_ldr_data in range(len(search_data_list['ldr_data'])):
#   if(count_ldr_data == 0):
#     # ldr_data_array.append(1003)
#     ldr_data_array.append(942)
#   else:
#     ldr_data_array.append(search_data_list['ldr_data'][count_ldr_data])
# update_parameter={"$set":{"ldr_data":ldr_data_array}}

# #更改值
frame_hash_array = []
for count_frame_hash in range(len(search_data_list['frame_hash'])):
  if(count_frame_hash == 0):
    # frame_hash_array.append('2c17b74d95d8dfc2858eef72208ed0fcafac2b4237a4c3012678c5f604a70466')
    frame_hash_array.append('2c17b74d95d8dfc2858eef72208ed0fcafac2b4237a4c3012678c5f604a70483')
  else:
    frame_hash_array.append(search_data_list['frame_hash'][count_frame_hash])
update_parameter={"$set":{"frame_hash":frame_hash_array}}


search_update_condition={"_id":ObjectId(search_data_list['_id'])}
realtime_data_id = realtime_sensor_data.update(search_update_condition, update_parameter)
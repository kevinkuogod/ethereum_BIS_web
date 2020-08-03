import pymongo
from bson.objectid import ObjectId
client = pymongo.MongoClient("localhost",27017)
db = client.iotsensordata

trace_asset_in_leagersite = db.trace_asset_in_leagersite
search_data_condition={"_id":ObjectId('5ebbf3aa4346b9369fde9672')}
search_data_list=trace_asset_in_leagersite.find(search_data_condition)[0]
find_leager_site_index = 0
search_update_condition={"_id":ObjectId(search_data_list['_id'])}

#更改值(選擇一)
# update_parameter={"$set":{"_id":ObjectId('5ebbf3aa4346b9369fde9699')}}

#更改值(選擇二)
#tea1
#Desiccant1
#wrapper1
# update_parameter={"$set":{"asset_RFID":'tea1'}}

#更改值(選擇三)
leager_site_id_array = []
for count_leager_site_id in range(len(search_data_list['leager_site_id_array'])):
  if(count_leager_site_id == 0):
    leager_site_id_array.append('5ebbe7949f78fc20bab75a86')
    # leager_site_id_array.append('5ebbe7949f78fc20bab75888')
  else:
    leager_site_id_array.append(search_data_list['leager_site_id_array'][count_leager_site_id])
# leager_site_id_array.append('5ebbe7949f78fc20bab75a86')
# leager_site_id_array.append('5ebbe7949f78fc20bab75888')
update_parameter={"$set":{"leager_site_id_array":leager_site_id_array}}

#某個供應鏈階段增加值(選擇四)
# error_0_array=search_data_list['error'][0]
# error_0_array.append('error')
# update_parameter={"$set":{"error.0":error_0_array}}

#某個供應鏈階段增加值(選擇五)
# error_item_0_array=search_data_list['error_item'][0]
# error_item_0_array.append('error_item')
# update_parameter={"$set":{"error_item.0":error_item_0_array}}

#某個供應鏈階段增加值(選擇六)
# error_time_0_array=search_data_list['error_time'][0]
# error_time_0_array.append('error_time')
# update_parameter={"$set":{"error_time.0":error_time_0_array}}

#更改值(選擇七)
  # update_parameter={"$set":{"create_time":'tea1'}}
#更改值
  # update_parameter={"$set":{"update_time":'tea1'}}

realtime_data_id = trace_asset_in_leagersite.update(search_update_condition, update_parameter)
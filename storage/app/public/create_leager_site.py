import pymongo
import datetime
# product_mertireal
leager_site_data_array = ['yieldy1','yieldy2','yieldy3','manufacture','delivery_place', 'retailer', 'buyer']
#connect db
client = pymongo.MongoClient("localhost",27017)
#choose db
db = client.iotsensordata
leager_site = db.leager_site
for leager_site_data in leager_site_data_array: 
    leager_site_data_id = leager_site.insert_one({'leager_site_name':leager_site_data,\
  				                                  'leager_site_createtime':datetime.datetime.now(),\
                                                  'leager_site_updatetime':datetime.datetime.now()})
import numpy as np
import matplotlib.pyplot as plt

# T=100
# n=1
# # n=5
# # n=10
# Tx = []
# plt.figure(figsize=(10, 4), dpi=500)  # 图片长宽和清晰度

# for i in range(int(T/n)):
#     Tx.append(i)
# ldr_gas_total = []
# ldr_field=1
# camera_gas_total = []
# camera_field=2
# smart_contract_basic_cost = 21784
# for i in range(int(T/n)):
#     ldr_gas_total.append(smart_contract_basic_cost+(256*ldr_field*i*64))
#     camera_gas_total.append(smart_contract_basic_cost+(256*camera_field*i*64))

# plt.step(Tx, ldr_gas_total, label='ldr')
# plt.plot(Tx, ldr_gas_total, 'o--', color='grey', alpha=0.3)

# plt.step(Tx, camera_gas_total, label='camera')
# plt.plot(Tx, camera_gas_total, 'o--', color='grey', alpha=0.3)

# plt.xlabel("Tx number(n=1)")
# # plt.xlabel("Tx number(n=5)")
# # plt.xlabel("Tx number(n=10)")
# plt.ylabel("gas number")
# plt.grid(axis='x', color='0.95')
# plt.legend(title='IOT:')
# plt.title('cost(with BIS)')
# plt.savefig("python_BIS_n_1.png")
# # plt.savefig("python_BIS_n_5.png")      # 保存图片
# # plt.savefig("python_BIS_n_10.png")
# plt.yscale('log')
# plt.show()

#1e10

# T=100
# n=1
# Tx = []
# plt.figure(figsize=(10, 4), dpi=500)  # 图片长宽和清晰度
# for i in range(int(T/n)):
#      Tx.append(i)

# ldr_gas_total = []
# camera_gas_total = []
# smart_contract_basic_cost = 21784
# #76187 ldr number(5秒)  15237(1秒)
# ldr_field=15237  
# #133 tag數量(5秒) 26(1秒)
# #15144045 flag數量(5秒) 3,028,809(1秒)
# #98339914 video大小
# camera_field=26+3028809
# for i in range(int(T/n)):
#     ldr_gas_total.append(smart_contract_basic_cost+(ldr_field*i*64))
#     if((i+1) == int(T/n)):
#         #camera_gas_total.append(smart_contract_basic_cost+((camera_field+98339914)*i*64))
#         camera_gas_total.append(smart_contract_basic_cost+((camera_field)*i*64))
#     else:
#         camera_gas_total.append(smart_contract_basic_cost+(camera_field*i*64))

# plt.step(Tx, ldr_gas_total, label='ldr')
# plt.plot(Tx, ldr_gas_total, 'o--', color='grey', alpha=0.3)

# plt.step(Tx, camera_gas_total, label='camera')
# plt.plot(Tx, camera_gas_total, 'o--', color='grey', alpha=0.3)

# plt.xlabel("time")
# plt.ylabel("gas number")
# plt.grid(axis='x', color='0.95')
# plt.legend(title='IOT:')
# plt.title('cost(without BIS)')
# plt.savefig("python_BS.png")
# # plt.yscale('log')
# plt.show()

#-------------------------------------------------------------------------------------------------------------------------------------------------
# T=100
# # n=1
# # n=5
# n=10
# Tx = []
# plt.figure(figsize=(7, 6), dpi=500)  # 图片长宽和清晰度

# for i in range(int(T/n)):
#     Tx.append(i)
# ldr_gas_total = []
# ldr_field=1
# camera_gas_total = []
# camera_field=2
# smart_contract_basic_cost = 21784
# for i in range(int(T/n)):
#     ldr_gas_total.append(smart_contract_basic_cost+(256*ldr_field*i*64))
#     camera_gas_total.append(smart_contract_basic_cost+(256*camera_field*i*64))

# plt.plot(Tx, ldr_gas_total, '^', color='grey', label='ldr')

# plt.plot(Tx, camera_gas_total, 'o', color='green', label='camera')

# # plt.xlabel("Tx number(n=1)")
# # plt.xlabel("Tx number(n=5)")
# plt.xlabel("Tx number(n=10)")
# plt.ylabel("gas number")
# plt.grid(axis='x', color='0.95')
# plt.legend(title='IOT:')
# plt.title('cost(with BIS)')
# # plt.savefig("python_BIS_n_1_v2.png")
# # plt.savefig("python_BIS_n_5_v2.png")      # 保存图片
# plt.savefig("python_BIS_n_10_v2.png")
# # plt.yscale('log')
# plt.show()


T=100
n=1
Tx = []
plt.figure(figsize=(7, 6), dpi=500)  # 图片长宽和清晰度
for i in range(int(T/n)):
     Tx.append(i)

ldr_gas_total = []
camera_gas_total = []
smart_contract_basic_cost = 21784
#76187 ldr number(5秒)  15237(1秒)
ldr_field=15237  
#133 tag數量(5秒) 26(1秒)
#15144045 flag數量(5秒) 3,028,809(1秒)
#98339914 video大小
camera_field=26+3028809
for i in range(int(T/n)):
    print(smart_contract_basic_cost+(ldr_field*i*64))
    ldr_gas_total.append(smart_contract_basic_cost+(ldr_field*i*64))
    if((i+1) == int(T/n)):
        #camera_gas_total.append(smart_contract_basic_cost+((camera_field+98339914)*i*64))
        camera_gas_total.append(smart_contract_basic_cost+((camera_field)*i*64))
    else:
        camera_gas_total.append(smart_contract_basic_cost+(camera_field*i*64))

plt.plot(Tx, ldr_gas_total, '^', color='grey', label='ldr')

plt.plot(Tx, camera_gas_total, 'o', color='green', label='camera')

plt.xlabel("time")
plt.ylabel("gas number")
plt.grid(axis='x', color='0.95')
plt.legend(title='IOT:')
plt.title('cost(without BIS)')
plt.savefig("python_BS_v2.png")
# plt.yscale('log')
plt.show()

#正如您在y轴上方所见，它表示1e11意味着单位为100亿
#https://www.thinbug.com/q/50335690
#https://blog.csdn.net/bajiang7063/article/details/102145851
#https://vlight.me/2018/04/14/Numerical-Python-Plotting-and-Visualization/
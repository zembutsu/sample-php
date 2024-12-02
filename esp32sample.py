import network
import urequests
import json
from machine import Pin, reset
from dht import DHT11
import time

# Wi-Fi接続設定（タイムアウト付き）
def connect_wifi():
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    if not wlan.isconnected():
        print('Wi-Fiに接続中...')
        try:
            wlan.connect('SSID', 'PASSWORD')
            # 接続待機（最大30秒）
            timeout = 30
            while not wlan.isconnected() and timeout > 0:
                print('.', end='')
                time.sleep(1)
                timeout -= 1
            if timeout == 0:
                print('\nWi-Fi接続タイムアウト！再起動します...')
                time.sleep(1)
                reset()  # 再起動
        except Exception as e:
            print('Wi-Fi接続エラー:', e)
            time.sleep(1)
            reset()
    print('\nWi-Fi接続完了！')
    print('IPアドレス:', wlan.ifconfig()[0])
    return wlan

# データ送信（タイムアウトとリトライ処理付き）
def send_data(sensor, wlan):
    max_retries = 3
    retry_count = 0
    
    while retry_count < max_retries:
        try:
            # Wi-Fi接続確認
            if not wlan.isconnected():
                print("Wi-Fi再接続中...")
                connect_wifi()

            # センサーデータ取得
            sensor.measure()
            temp = sensor.temperature()
            hum = sensor.humidity()
            
            # データの準備
            data = {
                'payload': {
                    'temperature': temp,
                    'humidity': hum
                }
            }
            
            print(f"送信開始...（試行 {retry_count + 1}/{max_retries}）")
            
            # データを送信（タイムアウト5秒）
            response = urequests.post(
                # 'http://<IP_ADDR>:1880/sensor/data',
                'http://<IP_ADDR>/sample-php/data_writer.php',
                headers={'content-type': 'application/json'},
                data=json.dumps(data),
                timeout=5
            )
            
            # レスポンスの内容を確認
            print('レスポンス:', response.text)

            response.close()
            print(f'送信完了！ 温度: {temp}°C 湿度: {hum}%')
            return True
            
        except Exception as e:
            print(f'エラー（試行 {retry_count + 1}）:', e)
            retry_count += 1
            if retry_count < max_retries:
                print(f'{5}秒後にリトライします...')
                time.sleep(5)
            else:
                print('最大リトライ回数を超えました。再起動します...')
                time.sleep(1)
                reset()
    
    return False

# メインループ
def main():
    try:
        # 初期化
        sensor = DHT11(Pin(14))
        wlan = connect_wifi()
        print("メインループ開始")
        
        while True:
            if send_data(sensor, wlan):
                print("10秒待機...")
                time.sleep(10)
            else:
                print("エラーが発生したため、1分待機...")
                time.sleep(60)
                
    except Exception as e:
        print('重大なエラー:', e)
        time.sleep(1)
        reset()

# プログラム実行
if __name__ == "__main__":
    main()

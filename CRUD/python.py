import requests

url = "http://localhost/cs6040/get_user_info.php"
user_id = 1
data = {"user_id": user_id}

try:
    response = requests.post(url, data=data)

    if response.status_code == 200:
        user_data = response.json()
        print("User Data:", user_data)
    else:
        print(f"Failed to retrieve user data. Status code: {response.status_code}")
        print("Error:", response.json().get("error", "Unknown error"))

except requests.exceptions.RequestException as e:
    print("An error occurred:", e)
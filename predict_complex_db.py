import numpy as np
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras.layers import Input, Dense, Concatenate, Flatten
from sklearn.model_selection import train_test_split
import sqlite3
import sys


user_identificator= sys.argv[0]
ip = sys.argv[1]
location = sys.argv[2]
db_probability = 0

# Connect to DB
conn = sqlite3.connect('mydatabase.db')
cursor = conn.cursor()

# GET DATA FROM DB

# IP address
sql_query = "SELECT avg_success_rate FROM ip_address_attempts WHERE ip = '" + ip + "'"
cursor.execute(sql_query)
average_success_rate_ip = cursor.fetchone()[0]


# Location
sql_query = "SELECT avg_success_rate FROM location_attempts WHERE location = '" + location + "'"
cursor.execute(sql_query)
average_success_rate_location = cursor.fetchone()[0]

db_probability = (average_success_rate_ip + average_success_rate_location) / 2

# data structure
# entrance: IP, location, duration, mouse speed
# exit: Success of login (0 = unseccesfull, 1 = successful)
ip_addresses = np.array()
locations = np.array()
duration = np.array()
mouse_speed = np.array()
success = np.array()

cursor.execute("SELECT * FROM login_attempts WHERE entity_identification = '" + user_identificator + "' ORDER BY date DESC")
for row in cursor.fetchall():
    duration.append(row[4])
    mouse_speed.append(row[2])
    ip_addresses.append(row[3])
    success.append(row[1])
    locations.append(row[5])



# Close db connection and cursor
cursor.close()
conn.close()

# Split data into train and test dataset
x_train, x_test, y_train, y_test = train_test_split(
    np.concatenate((ip_addresses, locations, duration, mouse_speed), axis=1),
    success,
    test_size=0.2,
    random_state=42
)

# Define model
ip_input = Input(shape=(1,))
location_input = Input(shape=(1,))
duration_input = Input(shape=(1,))
mouse_speed_input = Input(shape=(1,))

concatenated_inputs = Concatenate()([ip_input, location_input, duration_input, mouse_speed_input])

dense_layer_1 = Dense(64, activation='relu')(concatenated_inputs)
dense_layer_2 = Dense(64, activation='relu')(dense_layer_1)
output = Dense(1, activation='sigmoid')(dense_layer_2)

model = keras.Model(inputs=[ip_input, location_input, duration_input, mouse_speed_input], outputs=output)

# Compile model
model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

# Train model
model.fit([x_train[:, 0], x_train[:, 1], x_train[:, 2], x_train[:, 3]], y_train, epochs=10, batch_size=32)

# Evalute on test data
test_loss, test_accuracy = model.evaluate([x_test[:, 0], x_test[:, 1], x_test[:, 2], x_test[:, 3]], y_test)
print(f'Test L: {test_loss}')
print(f'Test A: {test_accuracy}')

# Predict success of login
ip_address_to_predict = np.array([['192.168.1.100']])
location_to_predict = np.array([['Ostrava']])
duration_to_predict = np.array([[5500]])
mouse_speed = np.array([[200]])

# get prediction of successful login
predicted_success_probability = model.predict([ip_address_to_predict, location_to_predict, duration_to_predict, mouse_speed])
print(f'Předpokládaná pravděpodobnost úspěchu přihlášení: {predicted_success_probability[0][0]}')

# Weights
weight1 = 0.4
weight2 = 0.6

weighted_average = (predicted_success_probability[0][0] * weight1) + (db_probability * weight2)
print(f'Vážený průměr: {weighted_average}')

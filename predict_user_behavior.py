import pandas as pd
import numpy as np
import tensorflow as tf
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score
import sqlite3
import sys

# Get user identificator from command
user_identificator= sys.argv[0]

# Connect to the SQLite database (create a new one if it doesn't exist)
conn = sqlite3.connect('scoring.db')

# Create a cursor object to interact with the database
cursor = conn.cursor()

# SQL query
cursor.execute("SELECT * FROM login_attempts WHERE entity_identification = '" + user_identificator + "' ORDER BY date DESC")


# Prepare data frame
data = pd.DataFrame({
    'duration': [],
    'mouse_speed': [],
    'ip_address': [],
    'time': [],
    'success': []
})

# Save data into dataset
for row in cursor.fetchall():
    data['duration'].append(row[4])
    data['mouse_speed'].append(row[2])
    data['ip_address'].append(row[3])
    data['time'].append(row[0])
    data['success'].append(row[1])


# Close cursor and connection
cursor.close()
conn.close()


# Convert ip addreseses
data['ip_address'] = pd.Categorical(data['ip_address']).codes

# Split data into training and test dataset
X = data.drop('success', axis=1)
y = data['success']
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Data normalization
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)

# Create model
model = tf.keras.Sequential([
    tf.keras.layers.Input(shape=(X_train_scaled.shape[1],)),
    tf.keras.layers.Dense(64, activation='relu'),
    tf.keras.layers.Dense(32, activation='relu'),
    tf.keras.layers.Dense(1, activation='sigmoid')
])

# Model compilation
model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

# Model training
model.fit(X_train_scaled, y_train, epochs=100, batch_size=2)

# New data to predict
new_data = np.array([[
    '2021-11-14 10:15:00',
    100,
    '192.168.1.100',
    5500
]])

# Prediction of successful login
prediction = model.predict(new_data)
print("Pravděpodobnost úspěšného přihlášení:", prediction[0][0])

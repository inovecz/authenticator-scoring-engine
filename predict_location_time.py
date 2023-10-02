import numpy as np
import tensorflow as tf
from tensorflow import keras
from sklearn.model_selection import train_test_split
import sqlite3


# Connect to the SQLite database (create a new one if it doesn't exist)
conn = sqlite3.connect('scoring.db')

# Create a cursor object to interact with the database
cursor = conn.cursor()

# SQL query
cursor.execute("SELECT * FROM login_attempts ORDER BY date DESC")

historical_data = []

# Save data into an array
for row in cursor.fetchall():
    historical_data.append(row)

# Close cursor and connection
cursor.close()
conn.close()

# Split data to test and traing dataset
X = historical_data[:, :-1]  # (IP, time, loc)
y = historical_data[:, -1]   # (success)

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Creation and compilation of model
model = keras.Sequential([
    keras.layers.Dense(64, activation='relu', input_shape=(X_train.shape[1],)),
    keras.layers.Dense(32, activation='relu'),
    keras.layers.Dense(1, activation='sigmoid')  # Sigmoid activation for binary classification
])

model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

# Traing model
model.fit(X_train, y_train, epochs=10, batch_size=32, validation_split=0.2)

# New data to predict
new_data = np.array([[192.168.1.1, '2021-09-01 14:45:00', 'Ostrava']])

# Prediction
prediction = model.predict(new_data)
print("Pravděpodobnost úspěšného přihlášení:", prediction[0][0])

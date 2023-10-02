import numpy as np
import tensorflow as tf
from tensorflow import keras
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler

# Prepared historical data (IP addreses and number of unsucessfull responses, target values: 0 = unsuccessful, 1 = successful)
IPs = np.array([["192.168.0.1", 3],
              ["192.168.0.2", 0],
              ["192.168.0.3", 2],
              ["192.168.0.4", 5],
              ["192.168.0.5", 1]])
target_values = np.array([0, 1, 0, 0, 1])

# Convert IP addresses to numeric values
ip_encoder = keras.layers.TextVectorization(output_mode="int", max_tokens=None)
ip_encoder.adapt(IPs[:, 0])
IPs_encoded = ip_encoder(IPs[:, 0])

# Standardization of number of unsucessful requests
scaler = StandardScaler()
IPs_scaled = scaler.fit_transform(IPs[:, 1].reshape(-1, 1))

# Merge encoded IP address and standardized data
IPs_combined = np.column_stack((IPs_encoded, IPs_scaled))

# Split data into traing and test dataset
IPs_train, IPs_test, target_values_train, target_values_test = train_test_split(IPs_combined, target_values, test_size=0.2, random_state=42)

# Model creation
model = keras.Sequential([
    keras.layers.Input(shape=(IPs_combined.shape[1],)),
    keras.layers.Dense(64, activation='relu'),
    keras.layers.Dense(32, activation='relu'),
    keras.layers.Dense(1, activation='sigmoid')
])

# Model compilation
model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

# Model training
model.fit(IPs_train, target_values_train, epochs=50, batch_size=4)

# Model evaluation
test_loss, test_accuracy = model.evaluate(IPs_test, target_values_test)
print(f"Test L: {test_loss:.4f}, Test A: {test_accuracy:.4f}")


# Prediction example for new login entry for test ip address, from which was number of unsuccessful responses: 3
new_login_data = np.array([["192.168.0.6", 3]])
new_login_encoded = ip_encoder(new_login_data[:, 0])
new_login_scaled = scaler.transform(new_login_data[:, 1].reshape(-1, 1))
new_login_combined = np.column_stack((new_login_encoded, new_login_scaled))
prediction = model.predict(new_login_combined)
print(f"Pravděpodobnost úspěšného přihlášení: {prediction[0][0]:.4f}")

import pandas as pd
import numpy as np
import tensorflow as tf
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score

# Prepare sample data
data = pd.DataFrame({
    'duration': [10, 15, 8, 12, 20, 5, 18, 25],
    'mouse_speed': [100, 120, 80, 110, 90, 95, 105, 115],
    'ip_address': ['192.168.1.1', '192.168.1.2', '192.168.1.1', '192.168.1.1', '192.168.1.2', '192.168.1.3', '192.168.1.3', '192.168.1.1'],
    'time': [9, 14, 7, 11, 19, 4, 17, 24],
    'success': [1, 1, 0, 1, 0, 1, 0, 0]
})

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

# Prediction on test data
y_pred = model.predict(X_test_scaled)
y_pred_binary = (y_pred > 0.5).astype(int)

# Model evaluation
accuracy = accuracy_score(y_test, y_pred_binary)
print(f'Přesnost: {accuracy}')

import numpy as np
import tensorflow as tf
from tensorflow import keras
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler

# Připravená historická data (X obsahuje IP adresy a počty neúspěšných pokusů, y je cílová proměnná: 0 = neúspěšný, 1 = úspěšný)
X = np.array([["192.168.0.1", 3],
              ["192.168.0.2", 0],
              ["192.168.0.3", 2],
              ["192.168.0.4", 5],
              ["192.168.0.5", 1]])
y = np.array([0, 1, 0, 0, 1])

# Převedení IP adres na číselné hodnoty
ip_encoder = keras.layers.TextVectorization(output_mode="int", max_tokens=None)
ip_encoder.adapt(X[:, 0])
X_encoded = ip_encoder(X[:, 0])

# Standardizace počtu neúspěšných pokusů
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X[:, 1].reshape(-1, 1))

# Spojení zakódovaných IP adres a standardizovaných dat
X_combined = np.column_stack((X_encoded, X_scaled))

# Rozdělení dat na tréninkovou a testovací sadu
X_train, X_test, y_train, y_test = train_test_split(X_combined, y, test_size=0.2, random_state=42)

# Vytvoření modelu
model = keras.Sequential([
    keras.layers.Input(shape=(X_combined.shape[1],)),
    keras.layers.Dense(64, activation='relu'),
    keras.layers.Dense(32, activation='relu'),
    keras.layers.Dense(1, activation='sigmoid')
])

# Kompilace modelu
model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

# Trénování modelu
model.fit(X_train, y_train, epochs=50, batch_size=4)

# Vyhodnocení modelu
test_loss, test_accuracy = model.evaluate(X_test, y_test)
print(f"Test Loss: {test_loss:.4f}, Test Accuracy: {test_accuracy:.4f}")

# Příklad predikce pro nový pokus o login (IP adresa: "192.168.0.6", počet neúspěšných pokusů: 3)
new_login_data = np.array([["192.168.0.6", 3]])
new_login_encoded = ip_encoder(new_login_data[:, 0])
new_login_scaled = scaler.transform(new_login_data[:, 1].reshape(-1, 1))
new_login_combined = np.column_stack((new_login_encoded, new_login_scaled))
prediction = model.predict(new_login_combined)
print(f"Prediction for new login attempt: {prediction[0][0]:.4f}")

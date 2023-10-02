import tensorflow as tf
from tensorflow import keras
from tensorflow.keras.layers import Input, Dense, Embedding, LSTM, concatenate
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler, LabelEncoder
import numpy as np

# 'data' structure:
# 'ip_address', 'locality', 'login_success' - cols to store into about ip address, location and success
# 'login_attempts', 'login_duration', 'mouse_speed' - cols with user behavior
data = []

# Prepare data
scaler = StandardScaler()
data[['login_attempts', 'login_duration', 'mouse_speed']] = scaler.fit_transform(data[['login_attempts', 'login_duration', 'mouse_speed']])

encoder = LabelEncoder()
data['locality'] = encoder.fit_transform(data['locality'])

# Split data into training and test set
X = data[['ip_address', 'locality', 'login_attempts', 'login_duration', 'mouse_speed']]
y = data['login_success']

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Create model
input_ip = Input(shape=(1,))
embedding_ip = Embedding(input_dim=num_unique_ips, output_dim=10)(input_ip)
embedding_ip = Flatten()(embedding_ip)

input_locality = Input(shape=(1,))
embedding_locality = Embedding(input_dim=num_unique_localities, output_dim=10)(input_locality)
embedding_locality = Flatten()(embedding_locality)

input_behavior = Input(shape=(3,))
dense_behavior = Dense(64, activation='relu')(input_behavior)

merged_input = concatenate([embedding_ip, embedding_locality, dense_behavior])

dense_layer1 = Dense(128, activation='relu')(merged_input)
dense_layer2 = Dense(64, activation='relu')(dense_layer1)
output_layer = Dense(1, activation='sigmoid')(dense_layer2)

model = keras.Model(inputs=[input_ip, input_locality, input_behavior], outputs=output_layer)

# Compile model
model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

# Traing model
model.fit([X_train['ip_address'], X_train['locality'], X_train[['login_attempts', 'login_duration', 'mouse_speed']]], y_train, epochs=10, batch_size=32, validation_data=([X_test['ip_address'], X_test['locality'], X_test[['login_attempts', 'login_duration', 'mouse_speed']]], y_test))


# Create second model to predict based on user behavior
ip_input = Input(shape=(1,))
location_input = Input(shape=(1,))
duration_input = Input(shape=(1,))
mouse_speed_input = Input(shape=(1,))

concatenated_inputs = Concatenate()([ip_input, location_input, duration_input, mouse_speed_input])

dense_layer_1 = Dense(64, activation='relu')(concatenated_inputs)
dense_layer_2 = Dense(64, activation='relu')(dense_layer_1)
output = Dense(1, activation='sigmoid')(dense_layer_2)

model2 = keras.Model(inputs=[ip_input, location_input, duration_input, mouse_speed_input], outputs=output)

# Compile model
model2.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

# Train model
model2.fit([x_train[:, 0], x_train[:, 1], x_train[:, 2], x_train[:, 3]], y_train, epochs=10, batch_size=32)


# Create probability for both models
login_success_prob1 = model.predict([X_test['ip_address'], X_test['locality'], X_test[['login_attempts', 'login_duration', 'mouse_speed']]])

# Print data
print("Výsledek váženého průměru pravděpodobností:")
print(login_success_prob1)

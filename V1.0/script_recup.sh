#!/bin/bash

# --- Récupération d'une ligne de données MQTT ---
data=$(mosquitto_sub -h mqtt.iut-blagnac.fr -t "AM107/by-room/+/data" -C 1)

# --- Extraction des données ---
temp=$(echo "$data" | awk -F',' '{print $1}' | awk -F':' '{print $2}')
co2=$(echo "$data" | awk -F',' '{print $4}' | awk -F':' '{print $2}')
room=$(echo "$data" | awk -F',' '{print $12}' | awk -F':' '{print $2}' | tr -d '"} ')

# --- Affichage debug ---
echo "Température : $temp"
echo "CO2 : $co2"
echo "Salle détectée : '$room'"

# --- Date et heure ---
date=$(date +"%Y-%m-%d")
heure=$(date +"%H:%M:%S")

# --- Connexion MySQL ---
USER="adminA"
PASS="passroot"  # à modifier
HOST="localhost"
DB="sae23"
MYSQL="/opt/lampp/bin/mysql -h $HOST -u $USER -p$PASS $DB"

# --- Vérifier si la salle existe dans la table Salle ---
salle_exists=$(echo "SELECT COUNT(*) FROM Salle WHERE NomSalle = '$room';" | $MYSQL | tail -1)

if [[ $salle_exists -eq 0 ]]; then
    echo "➕ Salle '$room' absente, insertion dans la table Salle..."
    echo "INSERT INTO Salle (NomSalle) VALUES ('$room');" | $MYSQL
fi

# --- Vérifier si des capteurs sont enregistrés pour cette salle ---
capteur_count=$(echo "SELECT COUNT(*) FROM Capteur WHERE NomSalle = '$room';" | $MYSQL | tail -1)

# --- Générer noms de capteurs ---
capteur_temp="temp_${room}"
capteur_co2="co2_${room}"

if [[ $capteur_count -eq 0 ]]; then
    echo "ℹ️ Capteurs absents, insertion automatique..."
    echo "INSERT INTO Capteur (NomCap, Type, Unite, NomSalle) VALUES
        ('$capteur_temp', 'temperature', '°C', '$room'),
        ('$capteur_co2', 'co2', 'ppm', '$room');" | $MYSQL
    echo "✅ Capteurs créés pour la salle '$room'."
fi

# --- Insérer les mesures dans la table Mesure ---
echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_temp', '$temp');" | $MYSQL
echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_co2', '$co2');" | $MYSQL

echo "✅ Mesures insérées pour la salle '$room'."

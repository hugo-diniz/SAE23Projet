#!/bin/bash

# --- MySQL Connexion ---
USER="adminA"
PASS="passroot"  
HOST="localhost"
DB="sae23"
MYSQL="/opt/lampp/bin/mysql -h $HOST -u $USER -p$PASS $DB"

# --- Add UNIQUE constraint on (date, NomCap, valeur) ---
echo "ALTER TABLE Mesure DROP INDEX unique_mesure;" | $MYSQL 2>/dev/null
echo "ALTER TABLE Mesure ADD UNIQUE unique_mesure (date, NomCap, valeur);" | $MYSQL 2>/dev/null || true
echo " Contrainte UNIQUE sur Mesure (date, NomCap, valeur) ajoutée (ou déjà existante)."

# --- Automatic insertion of buildings ---
$MYSQL <<EOF
INSERT IGNORE INTO Batiment (NomBat, NomGest, MDPGest) VALUES
('E001', NULL, NULL),
('Batiment E', NULL, NULL),
('B212', NULL, NULL),
('Batiment B', NULL, NULL);
EOF
echo "🏢 Bâtiments insérés ou déjà existants."

# --- Tracking of processed sensors ---
got_E001=false
got_B105=false

# --- Storing the latest values to avoid local duplicates ---
last_temp_E001=""
last_co2_E001=""
last_temp_B105=""
last_co2_B105=""

echo " En attente de données pour E001 et B105..."

while [[ "$got_E001" = false || "$got_B105" = false ]]; do
    # --- Reading an MQTT line ---
    data=$(mosquitto_sub -h mqtt.iut-blagnac.fr -t "AM107/by-room/+/data" -C 1)

    # --- Data Extraction ---
    temp=$(echo "$data" | awk -F',' '{print $1}' | awk -F':' '{print $2}')
    co2=$(echo "$data" | awk -F',' '{print $4}' | awk -F':' '{print $2}')
    room=$(echo "$data" | awk -F',' '{print $12}' | awk -F':' '{print $2}' | tr -d '"} ')

    # --- Only the room E001 and B105---
    if [[ "$room" != "E001" && "$room" != "B105" ]]; then
        echo " Salle '$room' ignorée."
        continue
    fi

    echo "📡 Données reçues pour '$room' — Temp: $temp °C, CO2: $co2 ppm"
    
    date=$(date +"%Y-%m-%d")
    heure=$(date +"%H:%M:%S")

    salle_exists=$(echo "SELECT COUNT(*) FROM Salle WHERE NomSalle = '$room';" | $MYSQL -N)
    if [[ "$salle_exists" -eq 0 ]]; then
        echo " Salle '$room' absente, insertion dans la table Salle..."
        echo "INSERT INTO Salle (NomSalle) VALUES ('$room');" | $MYSQL
    fi

    capteur_temp="temp_${room}"
    capteur_co2="co2_${room}"

    capteur_temp_exists=$(echo "SELECT COUNT(*) FROM Capteur WHERE NomCap = '$capteur_temp';" | $MYSQL -N)
    capteur_co2_exists=$(echo "SELECT COUNT(*) FROM Capteur WHERE NomCap = '$capteur_co2';" | $MYSQL -N)

    if [[ "$capteur_temp_exists" -eq 0 || "$capteur_co2_exists" -eq 0 ]]; then
        echo " Capteurs absents, insertion automatique..."
        echo "INSERT IGNORE INTO Capteur (NomCap, Type, Unite, NomSalle) VALUES
            ('$capteur_temp', 'temperature', '°C', '$room'),
            ('$capteur_co2', 'co2', 'ppm', '$room');" | $MYSQL
        echo " Capteurs créés pour la salle '$room'."
    fi

    if [[ "$room" == "E001" ]]; then
        if [[ "$temp" != "$last_temp_E001" ]]; then
            if echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_temp', '$temp');" | $MYSQL 2>/dev/null; then
                echo " Température insérée pour 'E001'."
                last_temp_E001="$temp"
            else
                echo " Température déjà présente, insertion ignorée pour 'E001'."
            fi
        else
            echo " Température E001 inchangée, ignorée."
        fi
    elif [[ "$room" == "B105" ]]; then
        if [[ "$temp" != "$last_temp_B105" ]]; then
            if echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_temp', '$temp');" | $MYSQL 2>/dev/null; then
                echo " Température insérée pour 'B105'."
                last_temp_B105="$temp"
            else
                echo " Température déjà présente, insertion ignorée pour 'B105'."
            fi
        else
            echo " Température B105 inchangée, ignorée."
        fi
    fi

    if [[ "$room" == "E001" ]]; then
        if [[ "$co2" != "$last_co2_E001" ]]; then
            if echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_co2', '$co2');" | $MYSQL 2>/dev/null; then
                echo " CO2 inséré pour 'E001'."
                last_co2_E001="$co2"
            else
                echo " CO2 déjà présent, insertion ignorée pour 'E001'."
            fi
        else
            echo " CO2 E001 inchangé, ignoré."
        fi
    elif [[ "$room" == "B105" ]]; then
        if [[ "$co2" != "$last_co2_B105" ]]; then
            if echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_co2', '$co2');" | $MYSQL 2>/dev/null; then
                echo " CO2 inséré pour 'B105'."
                last_co2_B105="$co2"
            else
                echo " CO2 déjà présent, insertion ignorée pour 'B105'."
            fi
        else
            echo " CO2 B105 inchangé, ignoré."
        fi
    fi

    [[ "$room" == "E001" ]] && got_E001=true
    [[ "$room" == "B105" ]] && got_B105=true

    sleep 0.5
done

echo " Données collectées pour E001 et B105. Fin du script."

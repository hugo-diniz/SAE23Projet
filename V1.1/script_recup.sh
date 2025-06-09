#!/bin/bash

# --- Connexion MySQL ---
USER="adminA"
PASS="passroot"  # ⚠️ sécuriser ce mot de passe
HOST="localhost"
DB="sae23"
MYSQL="/opt/lampp/bin/mysql -h $HOST -u $USER -p$PASS $DB"

# --- Ajout contrainte UNIQUE sur (date, NomCap, valeur) ---
echo "ALTER TABLE Mesure DROP INDEX unique_mesure;" | $MYSQL 2>/dev/null
echo "ALTER TABLE Mesure ADD UNIQUE unique_mesure (date, NomCap, valeur);" | $MYSQL 2>/dev/null || true
echo "🔐 Contrainte UNIQUE sur Mesure (date, NomCap, valeur) ajoutée (ou déjà existante)."

# --- Insertion automatique des bâtiments ---
$MYSQL <<EOF
INSERT IGNORE INTO Batiment (NomBat, NomGest, MDPGest) VALUES
('E001', NULL, NULL),
('Batiment E', NULL, NULL),
('B212', NULL, NULL),
('Batiment B', NULL, NULL);
EOF
echo "🏢 Bâtiments insérés ou déjà existants."

# --- Suivi des capteurs traités ---
got_E001=false
got_B105=false

# --- Mémorisation des dernières valeurs pour éviter doublons locaux ---
last_temp_E001=""
last_co2_E001=""
last_temp_B105=""
last_co2_B105=""

echo "🔄 En attente de données pour E001 et B105..."

while [[ "$got_E001" = false || "$got_B105" = false ]]; do
    # --- Lecture d'une ligne MQTT ---
    data=$(mosquitto_sub -h mqtt.iut-blagnac.fr -t "AM107/by-room/+/data" -C 1)

    # --- Extraction des données ---
    temp=$(echo "$data" | awk -F',' '{print $1}' | awk -F':' '{print $2}')
    co2=$(echo "$data" | awk -F',' '{print $4}' | awk -F':' '{print $2}')
    room=$(echo "$data" | awk -F',' '{print $12}' | awk -F':' '{print $2}' | tr -d '"} ')

    # --- Ne prendre en compte que E001 et B105 ---
    if [[ "$room" != "E001" && "$room" != "B105" ]]; then
        echo "❌ Salle '$room' ignorée."
        continue
    fi

    echo "📡 Données reçues pour '$room' — Temp: $temp °C, CO2: $co2 ppm"

    # --- Date et heure ---
    date=$(date +"%Y-%m-%d")
    heure=$(date +"%H:%M:%S")

    # --- Vérifier et insérer la salle si nécessaire ---
    salle_exists=$(echo "SELECT COUNT(*) FROM Salle WHERE NomSalle = '$room';" | $MYSQL -N)
    if [[ "$salle_exists" -eq 0 ]]; then
        echo "➕ Salle '$room' absente, insertion dans la table Salle..."
        echo "INSERT INTO Salle (NomSalle) VALUES ('$room');" | $MYSQL
    fi

    # --- Noms de capteurs ---
    capteur_temp="temp_${room}"
    capteur_co2="co2_${room}"

    # --- Vérifier capteurs et insérer si besoin ---
    capteur_temp_exists=$(echo "SELECT COUNT(*) FROM Capteur WHERE NomCap = '$capteur_temp';" | $MYSQL -N)
    capteur_co2_exists=$(echo "SELECT COUNT(*) FROM Capteur WHERE NomCap = '$capteur_co2';" | $MYSQL -N)

    if [[ "$capteur_temp_exists" -eq 0 || "$capteur_co2_exists" -eq 0 ]]; then
        echo "ℹ️ Capteurs absents, insertion automatique..."
        echo "INSERT IGNORE INTO Capteur (NomCap, Type, Unite, NomSalle) VALUES
            ('$capteur_temp', 'temperature', '°C', '$room'),
            ('$capteur_co2', 'co2', 'ppm', '$room');" | $MYSQL
        echo "✅ Capteurs créés pour la salle '$room'."
    fi

    # --- Température : éviter doublons locaux + gérer doublons DB ---
    if [[ "$room" == "E001" ]]; then
        if [[ "$temp" != "$last_temp_E001" ]]; then
            if echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_temp', '$temp');" | $MYSQL 2>/dev/null; then
                echo "✅ Température insérée pour 'E001'."
                last_temp_E001="$temp"
            else
                echo "⚠️ Température déjà présente, insertion ignorée pour 'E001'."
            fi
        else
            echo "⚠️ Température E001 inchangée, ignorée."
        fi
    elif [[ "$room" == "B105" ]]; then
        if [[ "$temp" != "$last_temp_B105" ]]; then
            if echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_temp', '$temp');" | $MYSQL 2>/dev/null; then
                echo "✅ Température insérée pour 'B105'."
                last_temp_B105="$temp"
            else
                echo "⚠️ Température déjà présente, insertion ignorée pour 'B105'."
            fi
        else
            echo "⚠️ Température B105 inchangée, ignorée."
        fi
    fi

    # --- CO2 : même principe que température ---
    if [[ "$room" == "E001" ]]; then
        if [[ "$co2" != "$last_co2_E001" ]]; then
            if echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_co2', '$co2');" | $MYSQL 2>/dev/null; then
                echo "✅ CO2 inséré pour 'E001'."
                last_co2_E001="$co2"
            else
                echo "⚠️ CO2 déjà présent, insertion ignorée pour 'E001'."
            fi
        else
            echo "⚠️ CO2 E001 inchangé, ignoré."
        fi
    elif [[ "$room" == "B105" ]]; then
        if [[ "$co2" != "$last_co2_B105" ]]; then
            if echo "INSERT INTO Mesure (date, heure, NomCap, valeur) VALUES ('$date', '$heure', '$capteur_co2', '$co2');" | $MYSQL 2>/dev/null; then
                echo "✅ CO2 inséré pour 'B105'."
                last_co2_B105="$co2"
            else
                echo "⚠️ CO2 déjà présent, insertion ignorée pour 'B105'."
            fi
        else
            echo "⚠️ CO2 B105 inchangé, ignoré."
        fi
    fi

    # --- Marquer la salle comme traitée ---
    [[ "$room" == "E001" ]] && got_E001=true
    [[ "$room" == "B105" ]] && got_B105=true

    # Facultatif : petite pause pour éviter spam
    sleep 0.5
done

echo "🏁 Données collectées pour E001 et B105. Fin du script."

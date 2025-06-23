import java.io.FileReader;
import java.util.LinkedHashMap;
import java.util.Map;
import com.opencsv.CSVReader;

public class CsvReader {
    public static Map<String, Double> lireDonnees(String chemin) {
        Map<String, Double> donnees = new LinkedHashMap<>();
        try (CSVReader reader = new CSVReader(new FileReader(chemin))) {
            String[] ligne;
            boolean premiereLigne = true;
            while ((ligne = reader.readNext()) != null) {
                if (premiereLigne) { premiereLigne = false; continue; }
                if (ligne.length < 2 || ligne[0].isEmpty()) continue;
                donnees.put(ligne[0], Double.parseDouble(ligne[1]));
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        return donnees;
    }
} 
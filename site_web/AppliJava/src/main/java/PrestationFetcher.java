import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.List;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import java.lang.reflect.Type;

public class PrestationFetcher {
    public static List<Prestation> fetchAll(String token) throws Exception {
        String apiUrl = "http://localhost/site_web/api/client/prestations/get.php";
        URL url = new URL(apiUrl);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("GET");
        conn.setRequestProperty("Accept", "application/json");
        if (token != null && !token.isEmpty()) {
            conn.setRequestProperty("Authorization", "Bearer " + token);
        }
        int responseCode = conn.getResponseCode();
        System.out.println("[PrestationFetcher] HTTP Response: " + responseCode);
        BufferedReader in;
        if (responseCode >= 200 && responseCode < 300) {
            in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
        } else {
            in = new BufferedReader(new InputStreamReader(conn.getErrorStream()));
        }
        StringBuilder content = new StringBuilder();
        String inputLine;
        while ((inputLine = in.readLine()) != null) {
            content.append(inputLine);
        }
        in.close();
        conn.disconnect();
        System.out.println("[PrestationFetcher] Contenu brut: " + content);
        if (responseCode >= 200 && responseCode < 300) {
            Gson gson = new Gson();
            Type listType = new TypeToken<List<Prestation>>(){}.getType();
            List<Prestation> prestations = gson.fromJson(content.toString(), listType);
            return prestations;
        } else {
            throw new RuntimeException("Erreur API Prestation: " + content);
        }
    }

    @Deprecated
    public static List<Prestation> fetchAll() throws Exception {
        return fetchAll("");
    }
} 
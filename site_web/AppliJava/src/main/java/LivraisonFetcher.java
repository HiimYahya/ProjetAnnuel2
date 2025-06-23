import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.List;
import com.google.gson.Gson;
import com.google.gson.JsonObject;
import com.google.gson.reflect.TypeToken;
import java.lang.reflect.Type;

public class LivraisonFetcher {
    public static List<Livraison> fetchAll(String token) throws Exception {
        String apiUrl = "http://localhost/site_web/api/admin/livraisons/get.php";
        URL url = new URL(apiUrl);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("GET");
        conn.setRequestProperty("Accept", "application/json");
        if (token != null && !token.isEmpty()) {
            conn.setRequestProperty("Authorization", "Bearer " + token);
        }
        int responseCode = conn.getResponseCode();
        System.out.println("[LivraisonFetcher] HTTP Response: " + responseCode);
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
        System.out.println("[LivraisonFetcher] Contenu brut: " + content);
        if (responseCode >= 200 && responseCode < 300) {
            Gson gson = new Gson();
            JsonObject jsonObject = gson.fromJson(content.toString(), JsonObject.class);
            Type listType = new TypeToken<List<Livraison>>(){}.getType();
            List<Livraison> livraisons = gson.fromJson(jsonObject.getAsJsonArray("livraisons"), listType);
            return livraisons;
        } else {
            throw new RuntimeException("Erreur API Livraison: " + content);
        }
    }

    @Deprecated
    public static List<Livraison> fetchAll() throws Exception {
        return fetchAll("");
    }
} 
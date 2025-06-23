import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.List;
import com.google.gson.Gson;
import com.google.gson.JsonObject;
import com.google.gson.reflect.TypeToken;
import java.lang.reflect.Type;

public class UtilisateurFetcher {
    public static List<Utilisateur> fetchAll(String token) throws Exception {
        String apiUrl = "http://localhost/site_web/api/admin/utilisateurs/get.php";
        URL url = new URL(apiUrl);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("GET");
        conn.setRequestProperty("Accept", "application/json");
        if (token != null && !token.isEmpty()) {
            conn.setRequestProperty("Authorization", "Bearer " + token);
        }

        int responseCode = conn.getResponseCode();
        System.out.println("[UtilisateurFetcher] HTTP Response: " + responseCode);
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
        System.out.println("[UtilisateurFetcher] Contenu brut: " + content);
        if (responseCode >= 200 && responseCode < 300) {
            Gson gson = new Gson();
            JsonObject jsonObject = gson.fromJson(content.toString(), JsonObject.class);
            Type listType = new TypeToken<List<Utilisateur>>(){}.getType();
            List<Utilisateur> users = gson.fromJson(jsonObject.getAsJsonArray("users"), listType);
            return users;
        } else {
            throw new RuntimeException("Erreur API Utilisateur: " + content);
        }
    }

    @Deprecated
    public static List<Utilisateur> fetchAll() throws Exception {
        return fetchAll("");
    }
} 
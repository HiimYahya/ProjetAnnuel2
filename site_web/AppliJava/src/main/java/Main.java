import javax.swing.*;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.util.Map;
import org.knowm.xchart.PieChart;
import org.knowm.xchart.XChartPanel;
import java.util.List;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import org.knowm.xchart.CategoryChart;
import org.knowm.xchart.CategoryChartBuilder;
import org.knowm.xchart.PieChartBuilder;
import java.util.LinkedHashMap;
import java.util.Comparator;
import java.util.stream.Collectors;

public class Main {
    public static void main(String[] args) throws Exception {
        String token = "";
        try {
            // Authentification et récupération du token JWT
            String loginUrl = "http://localhost/site_web/api/public/auth/login.php";
            URL url = new URL(loginUrl);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("POST");
            conn.setRequestProperty("Content-Type", "application/json");
            conn.setDoOutput(true);
            String jsonInputString = "{\"email\":\"admin@admin.com\",\"password\":\"admin@admin.com\"}";
            try(OutputStream os = conn.getOutputStream()) {
                byte[] input = jsonInputString.getBytes("utf-8");
                os.write(input, 0, input.length);
            }
            int responseCode = conn.getResponseCode();
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
            JsonObject jsonObject = JsonParser.parseString(content.toString()).getAsJsonObject();
            if (jsonObject.has("token")) {
                token = jsonObject.get("token").getAsString();
                System.out.println("[AUTH] Token JWT récupéré");
            } else {
                System.out.println("[AUTH] Erreur lors de la récupération du token : " + content);
            }
        } catch (Exception e) {
            System.out.println("[AUTH] Exception lors de la récupération du token : " + e.getMessage());
        }
        try {
            // Récupération des données API
            List<Utilisateur> utilisateurs = UtilisateurFetcher.fetchAll(token);
            List<Livraison> livraisons = LivraisonFetcher.fetchAll(token);
            List<Prestation> prestations = PrestationFetcher.fetchAll(token);

            // 1. Statistiques utilisateurs : répartition des rôles
            Map<String, Double> repartitionRoles = utilisateurs.stream()
                .collect(Collectors.groupingBy(u -> u.role, Collectors.counting()))
                .entrySet().stream().collect(Collectors.toMap(
                    Map.Entry::getKey,
                    e -> e.getValue().doubleValue()
                ));

            // 2. Top 5 clients les plus fidèles (ceux qui ont le plus de livraisons)
            Map<String, Long> livraisonsParClient = livraisons.stream()
                .collect(Collectors.groupingBy(l -> l.client_email, Collectors.counting()));
            Map<String, Double> top5Clients = livraisonsParClient.entrySet().stream()
                .sorted(Map.Entry.<String, Long>comparingByValue().reversed())
                .limit(5)
                .collect(Collectors.toMap(
                    Map.Entry::getKey,
                    e -> e.getValue().doubleValue(),
                    (e1, e2) -> e1,
                    LinkedHashMap::new
                ));

            // 3. Statistiques livraisons : répartition par statut
            Map<String, Double> repartitionStatutsLivraisons = livraisons.stream()
                .collect(Collectors.groupingBy(l -> l.statut, Collectors.counting()))
                .entrySet().stream().collect(Collectors.toMap(
                    Map.Entry::getKey,
                    e -> e.getValue().doubleValue()
                ));

            // 4. Statistiques prestations : répartition par statut
            Map<String, Double> repartitionStatutsPrestations = prestations.stream()
                .collect(Collectors.groupingBy(p -> p.statut, Collectors.counting()))
                .entrySet().stream().collect(Collectors.toMap(
                    Map.Entry::getKey,
                    e -> e.getValue().doubleValue()
                ));

            // 5. Top 5 prestations les plus demandées (par description)
            Map<String, Long> prestationsParDesc = prestations.stream()
                .collect(Collectors.groupingBy(p -> p.description, Collectors.counting()));
            Map<String, Double> top5Prestations = prestationsParDesc.entrySet().stream()
                .sorted(Map.Entry.<String, Long>comparingByValue().reversed())
                .limit(5)
                .collect(Collectors.toMap(
                    Map.Entry::getKey,
                    e -> e.getValue().doubleValue(),
                    (e1, e2) -> e1,
                    LinkedHashMap::new
                ));

            // Génération des charts (images) et affichage UI uniquement si données présentes
            String imgRoles = "roles.png";
            String imgTopClients = "top_clients.png";
            String imgStatutsLivraisons = "statuts_livraisons.png";
            String imgStatutsPrestations = "statuts_prestations.png";
            String imgTopPrestations = "top_prestations.png";
            if (!repartitionRoles.isEmpty()) {
                ChartExporter.creerCamembert(repartitionRoles, imgRoles);
                JFrame f1 = new JFrame("Répartition des rôles");
                f1.add(new XChartPanel<>(ChartExporter.creerCamembertChart(repartitionRoles, "Répartition des rôles")));
                f1.pack(); f1.setVisible(true);
            } else {
                System.out.println("Aucune donnée pour la répartition des rôles.");
            }
            if (!top5Clients.isEmpty()) {
                ChartExporter.creerHistogramme(top5Clients, imgTopClients);
                JFrame f2 = new JFrame("Top 5 clients les plus fidèles");
                f2.add(new XChartPanel<>(ChartExporter.creerHistogrammeChart(top5Clients, "Top 5 clients les plus fidèles")));
                f2.pack(); f2.setVisible(true);
            } else {
                System.out.println("Aucune donnée pour le top 5 clients.");
            }
            if (!repartitionStatutsLivraisons.isEmpty()) {
                ChartExporter.creerCamembert(repartitionStatutsLivraisons, imgStatutsLivraisons);
                JFrame f3 = new JFrame("Répartition des statuts de livraisons");
                f3.add(new XChartPanel<>(ChartExporter.creerCamembertChart(repartitionStatutsLivraisons, "Statuts livraisons")));
                f3.pack(); f3.setVisible(true);
            } else {
                System.out.println("Aucune donnée pour la répartition des statuts de livraisons.");
            }
            if (!repartitionStatutsPrestations.isEmpty()) {
                ChartExporter.creerCamembert(repartitionStatutsPrestations, imgStatutsPrestations);
                JFrame f4 = new JFrame("Répartition des statuts de prestations");
                f4.add(new XChartPanel<>(ChartExporter.creerCamembertChart(repartitionStatutsPrestations, "Statuts prestations")));
                f4.pack(); f4.setVisible(true);
            } else {
                System.out.println("Aucune donnée pour la répartition des statuts de prestations.");
            }
            if (!top5Prestations.isEmpty()) {
                ChartExporter.creerHistogramme(top5Prestations, imgTopPrestations);
                JFrame f5 = new JFrame("Top 5 prestations les plus demandées");
                f5.add(new XChartPanel<>(ChartExporter.creerHistogrammeChart(top5Prestations, "Top 5 prestations les plus demandées")));
                f5.pack(); f5.setVisible(true);
            } else {
                System.out.println("Aucune donnée pour le top 5 prestations.");
            }

            // Génération du PDF 2 pages uniquement avec les images générées
            String cheminPDF = "stats.pdf";
            java.util.List<String> page1 = new java.util.ArrayList<>();
            java.util.List<String> page2 = new java.util.ArrayList<>();
            if (new java.io.File(imgRoles).exists()) page1.add(imgRoles);
            if (new java.io.File(imgTopClients).exists()) page1.add(imgTopClients);
            if (new java.io.File(imgStatutsLivraisons).exists()) page1.add(imgStatutsLivraisons);
            if (new java.io.File(imgStatutsPrestations).exists()) page2.add(imgStatutsPrestations);
            if (new java.io.File(imgTopPrestations).exists()) page2.add(imgTopPrestations);
            if (!page1.isEmpty() || !page2.isEmpty()) {
                ChartExporter.exporterPDFMultiPages(
                    page1.toArray(new String[0]),
                    page2.toArray(new String[0]),
                    cheminPDF
                );
                JOptionPane.showMessageDialog(null, "PDF généré : " + cheminPDF);
            } else {
                System.out.println("Aucun graphique généré, PDF non créé.");
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
} 
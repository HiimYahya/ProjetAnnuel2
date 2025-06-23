import org.knowm.xchart.PieChart;
import org.knowm.xchart.PieChartBuilder;
import org.knowm.xchart.BitmapEncoder;
import org.knowm.xchart.BitmapEncoder.BitmapFormat;
import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.pdmodel.PDPage;
import org.apache.pdfbox.pdmodel.PDPageContentStream;
import org.apache.pdfbox.pdmodel.graphics.image.PDImageXObject;
import org.knowm.xchart.CategoryChart;
import org.knowm.xchart.CategoryChartBuilder;
import java.io.File;
import java.util.Map;

public class ChartExporter {
    public static String creerCamembert(Map<String, Double> donnees, String cheminImage) throws Exception {
        PieChart chart = new PieChartBuilder().width(600).height(400).title("Répartition des catégories").build();
        for (Map.Entry<String, Double> entry : donnees.entrySet()) {
            chart.addSeries(entry.getKey(), entry.getValue());
        }
        BitmapEncoder.saveBitmap(chart, cheminImage, BitmapFormat.PNG);
        return cheminImage;
    }

    public static String creerHistogramme(Map<String, Double> donnees, String cheminImage) throws Exception {
        CategoryChart chart = new CategoryChartBuilder().width(600).height(400).title("Histogramme des catégories").xAxisTitle("Catégorie").yAxisTitle("Pourcentage").build();
        chart.addSeries("Pourcentage", new java.util.ArrayList<>(donnees.keySet()), new java.util.ArrayList<>(donnees.values()));
        BitmapEncoder.saveBitmap(chart, cheminImage, BitmapFormat.PNG);
        return cheminImage;
    }

    public static void exporterPDF(String cheminCamembert, String cheminHistogramme, String cheminPDF) throws Exception {
        try (PDDocument doc = new PDDocument()) {
            PDPage page = new PDPage();
            doc.addPage(page);
            PDImageXObject pdImage1 = PDImageXObject.createFromFile(cheminCamembert, doc);
            PDImageXObject pdImage2 = PDImageXObject.createFromFile(cheminHistogramme, doc);
            try (PDPageContentStream contentStream = new PDPageContentStream(doc, page)) {
                contentStream.drawImage(pdImage1, 100, 400, 400, 300);
                contentStream.drawImage(pdImage2, 100, 50, 400, 300);
            }
            doc.save(cheminPDF);
        }
    }

    public static PieChart creerCamembertChart(Map<String, Double> donnees, String titre) {
        PieChart chart = new PieChartBuilder().width(600).height(400).title(titre).build();
        for (Map.Entry<String, Double> entry : donnees.entrySet()) {
            chart.addSeries(entry.getKey(), entry.getValue());
        }
        return chart;
    }

    public static CategoryChart creerHistogrammeChart(Map<String, Double> donnees, String titre) {
        CategoryChart chart = new CategoryChartBuilder().width(600).height(400).title(titre).xAxisTitle("Catégorie").yAxisTitle("Valeur").build();
        chart.addSeries("Valeur", new java.util.ArrayList<>(donnees.keySet()), new java.util.ArrayList<>(donnees.values()));
        return chart;
    }

    public static void exporterPDFMultiPages(String[] imagesPage1, String[] imagesPage2, String cheminPDF) throws Exception {
        try (PDDocument doc = new PDDocument()) {
            // Page 1
            PDPage page1 = new PDPage();
            doc.addPage(page1);
            try (PDPageContentStream cs1 = new PDPageContentStream(doc, page1)) {
                int y = 650;
                for (String img : imagesPage1) {
                    PDImageXObject pdImage = PDImageXObject.createFromFile(img, doc);
                    cs1.drawImage(pdImage, 100, y, 400, 200);
                    y -= 220;
                }
            }
            // Page 2
            PDPage page2 = new PDPage();
            doc.addPage(page2);
            try (PDPageContentStream cs2 = new PDPageContentStream(doc, page2)) {
                int y = 650;
                for (String img : imagesPage2) {
                    PDImageXObject pdImage = PDImageXObject.createFromFile(img, doc);
                    cs2.drawImage(pdImage, 100, y, 400, 200);
                    y -= 220;
                }
            }
            doc.save(cheminPDF);
        }
    }
} 
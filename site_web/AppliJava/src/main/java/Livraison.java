import java.util.List;

public class Livraison {
    public int id;
    public int id_client;
    public int id_livreur;
    public int id_annonce;
    public String statut;
    public String date_prise_en_charge;
    public String client_nom;
    public String client_email;
    public String livreur_nom;
    public String livreur_email;
    public String annonce_titre;
    public List<Segment> segments;
} 
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Domaine;
use App\Models\Discipline;

class DomaineDisciplineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapping Domaine => [Disciplines...]
        $data = [
            "Agriculture" => [
                "Agriculture et Business",
                "Agriculture et Sciences",
                "Agriculture et Eduction",
                "Production agricole et animale",
                "Agronomie",
                "Elevage",
                "Horticulture et jardinage",
            ],
            "Architecture et urbanisme" => [
                "Aménagement communautaire",
                "Aménagement des paysages",
                "Architecture",
                "Architecture de gros œuvres",
                "Cartographie",
            ],
            "Arts du spectacle" => [
                "Chant",
                "Cirque",
                "Danse",
                "Musique",
                "Théâtre",
            ],
            "Arts graphiques et audiovisuels" => [
                "Cinématographie",
                "Design",
                "Histoire de l'art",
                "Imprimerie et édition",
                "Jeux vidéos",
                "Multimédia",
                "Photographie",
                "Production de radio et de télévision",
                "Production musicale",
                "Webdesign",
            ],
            "Autres" => [
                "Autres",
            ],
            "Banque et assurance" => [
                "Audit",
                "Assurance",
                "Analyse des investissements",
                "Banque",
                "Finance",
                "Fiscalité Management",
            ],
            "Beaux-arts" => [
                "Artisanat",
                "Conservation",
                "Dessin",
                "Peinture",
                "Sculpture",
            ],
            "Commerce de détail" => [
                "Agences immobilières",
                "Marketing",
                "Publicité",
                "Relations publiques",
                "Vente",
            ],
            "Commerce et administration" => [
                "Administration des institutions",
                "Administration publique",
                "Entrepreunariat et leadership",
                "Gestion",
                "Logistique",
                "Ressources humaines",
            ],
            "Droit" => [
                "Droit (général; international; du travail; des affaires; des contrats; etc)",
                "Formation des magistrats et des notaires",
                "Histoire du droit",
                "Relations internationales",
                "Paralégal",
            ],
            "Formation des enseignants" => [
                "Formation des enseignants",
                "Orientation",
            ],
            "Génie civil" => [
                "Bâtiment",
                "Construction",
            ],
            "Industrie de transformation et de traitement" => [
                "Cuir",
                "Industries minières et extractives",
                "Matériaux (bois; papiers; plastique; verre; métal etc)",
                "Traitement des produits alimentaires et des boissions",
                "Textiles",
                "Vêtements",
            ],
            "Ingénierie et techniques apparentées" => [
                "Acoustique",
                "Aéronautique",
                "Automobile",
                "Dessin industriel",
                "Electricité",
                "Electronique",
                "Energie et génie chimique",
                "Ingénierie",
                "Mécanique",
                "Télécommunications",
                "Topographie",
            ],
            "Journalisme et information" => [
                "Bibliothéconomie",
                "Communication",
                "Formation de techniciens de musées et d'établissement analogue",
                "Formation technique aux bibliothèques",
                "Journalisme et reportage",
            ],
            "Langues autochtones" => [
                "Langues courantes ou vernaculaires et leur littérature",
            ],
            "Langues et cultures étrangères" => [
                "Etudes régionales interdisciplinaires",
                "Langues vivantes ou \"mortes\" et leur littérature",
            ],
            "Mathématiques et Statistiques" => [
                "Cybersécurité",
                "Data science",
                "Intelligence artificielle",
                "Mathématiques",
                "Sciences informatiques",
                "Statistiques",
            ],
            "Médecine" => [
                "Anatomie",
                "Anesthésiologie",
                "Chirurgie",
                "Cytologie",
                "Epidémiologie",
                "Génétique",
                "Hématologie",
                "Immunologie",
                "Médecine interne",
                "Neurologie",
                "Obstétrique et gynécologie",
                "Oncologie",
                "Ophtalmologie",
                "Pédiatrie",
                "Physiologie",
                "Psychiatrie",
                "Radiologie",
                "Sciences cognitives",
            ],
            "Protection de l'environnement" => [
                "Contrôle et protection de l’environnement",
                "Sciences environnementales",
                "Sciences de la terre",
            ],
            "Religion et théologie" => [
                "Religion",
                "Théologie",
            ],
            "Sciences de la vie" => [
                "Autres sciences apparentées à l'exclusion des sciences cliniques et vétériniaires",
                "Bactériologie",
                "Biochimie",
                "Biologie",
                "Botanique",
                "Biophysique",
                "Entomologie",
                "Génétique",
                "Microbiologie",
                "Ornithologie",
                "Toxicologie",
                "Zoologie",
            ],
            "Sciences de l'éducation" => [
                "Sciences de l'éducation",
            ],
            "Sciences humaines" => [
                "Archéologie",
                "Ecriture créative",
                "Histoires anciennes",
                "Histoire",
                "Interprétation et traduction",
                "Linguistique",
                "Littérature comparée",
                "Philosophie",
            ],
            "Sciences physiques" => [
                "Astronomie et sciences de l'espace",
                "Anthropologie physique",
                "Autres matières apparentées",
                "Chimie",
                "Climatologie",
                "Géographie physique et autres géosciences",
                "Géologie",
                "Géophysique",
                "Météorologie et autres sciences se rapportant à l'athmospère",
                "Minéralogie",
                "Océanographie",
                "Paléoécologie",
                "Physique",
                "Sciences appliquées",
                "Vulcanologie",
            ],
            "Sciences sociales et du comportement" => [
                "Anthropologie (à l'exception de l'anthropologie physique)",
                "Démographie",
                "Droit de l'homme",
                "Economie",
                "Ethnologie",
                "Etude sur la paix et les conflits",
                "Futurologie",
                "Histoire économique",
                "Géographie (à l'exception de la géographie physique)",
                "Sciences politiques et éducation civique",
                "Sciences sociales",
                "Sociologie et études culturelles",
                "Psychologie",
            ],
            "Sciences vétérinaires" => [
                "Sciences vétérinaires",
            ],
            "Service de sécurité" => [
                "Criminologie",
                "Protection et lutte contre les incendies",
                "Sécurité civile",
                "Sécurité militaire",
            ],
            "Services aux particuliers" => [
                "Coiffure",
                "Cosmétologie",
                "Cuisine et patisserie",
                "Hôtellerie et services de restauration",
                "Soins de beauté et autres services aux particuliers",
                "Sports et loisirs",
                "Voyage et tourisme",
            ],
            "Services de transport" => [
                "Contrôle du trafic aérien",
                "Formation d'équipages d'avions",
                "Formation de marins et d'officiers de marine",
                "Sciences nautiques",
                "Services postaux",
                "Transports ferroviaires",
                "Transports maritimes",
                "Transports routiers",
            ],
            "Services dentaires" => [
                "Assistant dentaire",
                "Dentiste",
                "Odontologie",
            ],
            "Services médicaux" => [
                "Nutrition",
                "Optométrie",
                "Pharmacologie",
                "Rééducation",
                "Services de santé publique",
            ],
            "Services sociaux" => [
                "Services gérontologiques",
                "Services pour la jeunesse",
                "Soins aux enfants",
                "Soins aux handicapés",
            ],
            "Soins infirmiers" => [
                "Formation de sages-femmes",
                "Soins infirmiers de base",
            ],
            "Sports" => [
                "Education physique",
                "Pratique de sport",
            ],
            "Stylisme" => [
                "Costume",
                "Stylisme et couture",
            ],
            "Technique de documentation" => [
                "Archivisme",
                "Formation aux bibliothèques et à la documentation",
                "Muséologie",
            ],
        ];

        // Insert data in a transaction
        DB::transaction(function () use ($data) {
            foreach ($data as $domaineName => $disciplines) {
                // Create or get the domaine
                $domaine = Domaine::firstOrCreate(
                    ['nom' => $domaineName],
                    ['nom' => $domaineName]
                );

                foreach ($disciplines as $disciplineName) {
                    $name = trim($disciplineName);
                    if ($name === '') {
                        continue;
                    }

                    // Create discipline linked to domaine if it doesn't exist
                    Discipline::firstOrCreate(
                        [
                            'nom' => $name,
                            'domaine_id' => $domaine->id
                        ],
                        [
                            'nom' => $name,
                            'domaine_id' => $domaine->id
                        ]
                    );
                }
            }
        });
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Comments;
use App\Entity\Posts;
use App\Entity\Tags;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

use function PHPUnit\Framework\stringContains;
use function Symfony\Component\String\u;

class AppFixtures extends Fixture
{
    // Constructeur de la classe :
    // et prend en paramètre @param : UserPasswordHasherInterface
    //$hasher qui est l'instance du service de hachage de mot de passe.
    // instance du service de slugger qui permet de sluggifier 
    // Ce constructor permet de'instancier les 2 méthodes suivantes utilisable dans le reste 
    // du code.
    public function __construct (
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SluggerInterface $slugger
    ){
    }

      // La fonction suivante permet de charger les données de base dans la base de données
    // et prend en paramètre @param : ObjetManager $manager est le gestionnaire d'identité pour l'opération de persistance
    public function load(ObjectManager $manager): void
    {
        // L'ordre est très important.
        $this->loadUsers($manager);
        $this->loadTags($manager);
        $this->loadPosts($manager);
    
    }

    

    // Charge les utilisateurs dans la base de données
     // et prend en paramètre @param : ObjetManager $manager est le gestionnaire d'identité pour l'opération de persistance
    //  return void
    private function  loadUsers(ObjectManager $manager): void 
    {
        // Parcourt les données des utilisateurs obtenues depuis une source quelconque.
        foreach($this->getUsersData() as [$fullname, $username, $password, $email, $roles])
        {
            // Création d'une nouvelle instance de l'entity Users.
            $user = (new Users())
            // Définition des propriétés de l'utilisateur.
            ->setFullName($fullname)
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles($roles);

            // Hachage du mot de passe
            $user->setPassword($this->hasher->hashPassword($user,$password));

            // Persistance de l'utilisateur (idem à un commit pour préparer les datas)
            $manager->persist($user);

            // Ajout d'une référence à l'utilisateur pour une utilisation ultérieure dans les test
            $this->addReference($username, $user);
        }
        // Execution des opérateurs de persistance.
        $manager->flush();
    }

    // Charge les tags dans la base de données
     // et prend en paramètre (@param) : ObjetManager $manager est le gestionnaire d'identité pour l'opération de persistance
    //  return void
    private function loadTags(ObjectManager $manager): void
    {
        // Parcourt les données des tags obtenues depuis une source quelconque.
        foreach($this->getTagsData() as $name) {
        // Création d'une nouvelle instance de l'entity Users.
        $tag = new Tags($name);
         // Persistance du tag (idem à un commit pour préparer les datas)
        $manager->persist($tag);
        // Ajout d'une référence au tag pour une utilisation ultérieure dans les test
        $this->addReference("tag-$name", $tag);
      }
         // Execution des opérateurs de persistance.
         $manager->flush();
}

 // Charge les publications fictives dans la base de données
     // et prend en paramètre @param : ObjetManager $manager est le gestionnaire d'identité pour l'opération de persistance
    //  return void
     private function loadPosts(ObjectManager $manager): void
    {
        // Parcourt les données de publication obtenues depuis une source quelconque.
        foreach($this->getPostData() as [$title, $slug, $summary, $content, $publishedAt,$author, $tags]) {
            // Création d'une nouvelle instance de l'entité Posts
            $post = (new Posts())
            // Définition des propriétés de la publication
                  ->setTitle($title)
                  ->setSlug($slug)
                  ->setSummary($summary)
                  ->setContent($content)
                  ->setPublishedAt($publishedAt)
                  ->setAuthor($author)
                // Ajout des tags à la publication
                  ->addTag(...$tags);
                // ...$tags prend chaque élément du tableau $tags et le traite comme un argument distinc.

                // Ajout de commentaire fictifs à la publication.
            foreach(range(1, 5) as $i) {
                // Récupération de l'auteur de commentaire fictif.
                /**@var Users $commentAuthor */
                $commentAuthor = $this->getReference('john_user');

                // Création d'une nouvelle instance de l'entité "Comments".
                $comment = (new Comments())
                        // Définition des propriétés du commentaire.
                         ->setAuthor($commentAuthor)
                         ->setContent($this->getRandomText(random_int(255, 512)))
                         ->setPublishedAt(new \DateTimeImmutable("now + $i minutes"))
                         ->setPost($post);
                
                        //  Presistance du commentaire (préparation avant de le pusher)
                $manager->persist($comment);
            }
            $manager->persist($post);
        }
        // Execution des opérations de persistence. (Envoie vers la base)
        $manager->flush();
    }

    // Génère des données de publication fictives pour les tests.
    //  @throws \Exception Si une erreur survient lors de la génération de données aléatoires.
    //  @return array<int, array{0: string, 1: STring, 2: string, 3: string,
    // 4: \DateTime, 5: Users, 6: array<Tags>}>
        private function getPostData(): array
    {
    // Initialise un tableau pour stocker les données de publication.
    $posts = [];
    
    // Parcourt les phrases obtenues depuis une surce quelconque.
    foreach($this->getPhrases() as $i => $title){
        // Génération de données pour chaque publication.

        // Récupération aléatoire d'un utilisateur de référence (Jane Doe ou Tom Doe => Admin) 
        // pour l'auteur de la publication.
        /**
         * @var Users $user
         */
    // Utilisation d'une condition ternaire : le "?" définie : si 0 est strictement égal à $i, alors on utilise 0, sinon on utilise de façon aléatoire les datas 0 et 1. Les ":" défini le "else".
        $user = $this->getReference(['jane_admin', 'tom_admin'] [0 === $i ? 0 : random_int(0,1)]);

        // Construction d'un tableau représentant les données de publication.
        $posts[] = [
            // 0 : Titre de la publication
            $title,
            // 1: Slug généré à partir du titre
            $this->slugger->slug($title)->lower(),
            // 2: Résumé aléatoire de la publication
            $this->getRandomText(),
            // 3: Contenu aléatoire de la publication
            $this->getPostContent(), 
            // 4: Date de publication calculée en fonction de l'index et de l'heure actuelle   
            (new \DateTimeImmutable('now - '. $i . 'days'))->setTime(random_int(8, 17),random_int(7, 49), random_int(0, 59)),
            //  5: Auteur de la publication
            $user,
            // 6: Tags aléatoire associés à la publication
            $this->getRandomTags(),
        ];
    }
    // Retourne le tableau de données de publication générée.
    return $posts;
    }

/**
 * @return array<array{string, string, string, string, array<string>}>
 */
    private function getUsersData(): array 
    {
        return [
            // $userData = [$fullName, $Uername, $password, $email, $role];
            ['Jane Doe', 'jane_admin', 'kitten','jane_admin@symfony.com',['ROLE_ADMIN']],
            ['Tom Doe', 'tom_admin', 'kitten','tom_admin@symfony.com',['ROLE_ADMIN']],
            ['John Doe', 'john_user', 'kitten','john_user@symfony.com',['ROLE_USER']]
        ];
    }

    # Création manuelle les données des tags

    /**
     * @return string[]
     */
    private function getTagsData(): array
    {
        return [
            'lorem',
            'ipsum',
            'consectetur',
            'adipiscing',
            'incididunt',
            'labore',
            'voluptate',
            'dolore',
            'pariatur',
        ];
    }

     /**
     * @return string[]
     */
    private function getPhrases(): array
    {
        return [
            'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'Pellentesque vitae velit ex',
            'Mauris dapibus risus quis suscipit vulputate',
            'Eros diam egestas libero eu vulputate risus',
            'In hac habitasse platea dictumst',
            'Morbi tempus commodo mattis',
            'Ut suscipit posuere justo at vulputate',
            'Ut eleifend mauris et risus ultrices egestas',
            'Aliquam sodales odio id eleifend tristique',
            'Urna nisl sollicitudin id varius orci quam id turpis',
            'Nulla porta lobortis ligula vel egestas',
            'Curabitur aliquam euismod dolor non ornare',
            'Sed varius a risus eget aliquam',
            'Nunc viverra elit ac laoreet suscipit',
            'Pellentesque et sapien pulvinar consectetur',
            'Ubi est barbatus nix',
            'Abnobas sunt hilotaes de placidus vita',
            'Ubi est audax amicitia',
            'Eposs sunt solems de superbus fortis',
            'Vae humani generis',
            'Diatrias tolerare tanquam noster caesium',
            'Teres talis saepe tractare de camerarius flavum sensorem',
            'Silva de secundus galatae demitto quadra',
            'Sunt accentores vitare salvus flavum parses',
            'Potus sensim ad ferox abnoba',
            'Sunt seculaes transferre talis camerarius fluctuies',
            'Era brevis ratione est',
            'Sunt torquises imitari velox mirabilis medicinaes',
            'Mineralis persuadere omnes finises desiderium',
            'Bassus fatalis classiss virtualiter transferre de flavum',
        ];
    }


    private function getPostContent(): string
    {
        return <<<'MARKDOWN'
            Lorem ipsum dolor sit amet consectetur adipisicing elit, sed do eiusmod tempor
            incididunt ut labore et **dolore magna aliqua**: Duis aute irure dolor in
            reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
            deserunt mollit anim id est laborum.

              * Ut enim ad minim veniam
              * Quis nostrud exercitation *ullamco laboris*
              * Nisi ut aliquip ex ea commodo consequat

            Praesent id fermentum lorem. Ut est lorem, fringilla at accumsan nec, euismod at
            nunc. Aenean mattis sollicitudin mattis. Nullam pulvinar vestibulum bibendum.
            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos
            himenaeos. Fusce nulla purus, gravida ac interdum ut, blandit eget ex. Duis a
            luctus dolor.

            Integer auctor massa maximus nulla scelerisque accumsan. *Aliquam ac malesuada*
            ex. Pellentesque tortor magna, vulputate eu vulputate ut, venenatis ac lectus.
            Praesent ut lacinia sem. Mauris a lectus eget felis mollis feugiat. Quisque
            efficitur, mi ut semper pulvinar, urna urna blandit massa, eget tincidunt augue
            nulla vitae est.

            Ut posuere aliquet tincidunt. Aliquam erat volutpat. **Class aptent taciti**
            sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Morbi
            arcu orci, gravida eget aliquam eu, suscipit et ante. Morbi vulputate metus vel
            ipsum finibus, ut dapibus massa feugiat. Vestibulum vel lobortis libero. Sed
            tincidunt tellus et viverra scelerisque. Pellentesque tincidunt cursus felis.
            Sed in egestas erat.

            Aliquam pulvinar interdum massa, vel ullamcorper ante consectetur eu. Vestibulum
            lacinia ac enim vel placerat. Integer pulvinar magna nec dui malesuada, nec
            congue nisl dictum. Donec mollis nisl tortor, at congue erat consequat a. Nam
            tempus elit porta, blandit elit vel, viverra lorem. Sed sit amet tellus
            tincidunt, faucibus nisl in, aliquet libero.
            MARKDOWN;
    }

    /**
     * Génère du texte aléatoire en concaténant des phrases aléatoires
     * jusq'à atteindre une longueur maximale spécifiée.
     * 
     * paramètre @param int $maxLenght La longueur maximale du texte généré. Par défaut 255.
     * @return string Le texte aléatoire généré.
     */
    private function getRandomText(int $maxLenght = 255): string
    {
        // Récupération des phrars à partir d'une source quelconque.
        $phrases = $this->getphrases();
        // Mélange aléatoire des phrases.
        shuffle($phrases);
        
        //Concaténation des phrases mélangéz jusqu'à atteindre la longueur maximale spécifiée.
        do{
            // Concaténation des phrases avec un point d'arrêt à la fin de chaque phrase.
            // utilisation de la bibliothèque "PHP microtime uString" 
            $text = u('. ')->join($phrases)->append('.');
            // Suppression de la dernière phrase du tableau pour raccourcir  le texte.
            array_pop($phrases);
        } while($text->length() > $maxLenght); //Vérification de la longueur du texte généré.

        // Retourne le texte généré.
        return $text;
    }

     /**
     * Génère un tableau d'objets tag aléatoire.
     * 
     * @throws \Exception Si une erreur survient lors de la génération des nombres aléatoires.
     * @return array<Tags> Un tableau d'objet Tags aléatoire.
     */
    private function getRandomTags(): array {
        //Récupération des noms de Tags à partir d'une source quelconque.
        $tagNames = $this->getTagsData();
        // Mélange aléatoire des noms de tag.
        shuffle($tagNames);
        // Sélection aléatoire de 2 à 4 noms de tag.
        // Le \ signifie que l'on veut utiliser la fonction native array_slice de PHP. Cela permet d'aller chercher la methode array_slice chez PHP directement.
        $selectedTags = \array_slice($tagNames, 0,random_int(2,4));
        // création d'un tableau d'obejt tag à partir des noms de tags sélectionnés.
        return array_map(function($tagName){
            // Récupération de l'objet tag correspondant au nom de tagg à partir de références.
            /**@var Tags $tag */
            $tag = $this->getReference("tag-$tagName");
            return $tag;
        }, $selectedTags);
    }
}
# Exécution des slides — School Management

Ce README explique comment afficher et exporter le deck de slides Markdown créé dans `slides/slides.md`.

Pré-requis recommandés

- Node.js >= 16
- npm
- (Optionnel) Pandoc + LaTeX si export Beamer/PDF
- (Optionnel) Google Chrome pour export PDF via impression

Méthode A — reveal-md (recommandé pour présentation interactive)

1. Installer `reveal-md` globalement:

```bash
npm install -g reveal-md
```

2. Lancer le serveur local et ouvrir le navigateur:

```bash
cd c:/Users/omrac/Desktop/my_projet
reveal-md slides/slides.md --title "School Management"
```

3. Afficher en local sur `http://localhost:1948` (par défaut).

Exporter en HTML statique:

```bash
reveal-md slides/slides.md --static slides/html
```

Méthode B — Export PDF (options)

- Option 1 (via Chrome): Ouvrir la présentation avec `reveal-md` puis Fichier → Imprimer → Enregistrer au format PDF (ou `Ctrl+P`).

- Option 2 (pandoc → beamer): (résultat Beamer, différent visuel)

```bash
# Installer pandoc + LaTeX
pandoc -t beamer slides/slides.md -o slides.pdf
```

- Option 3 (headless Chrome): utiliser `puppeteer` script ou `decktape` pour exporter en PDF (qualité élevée pour reveal.js):

```bash
# Exemple avec decktape (node)
npm install -g decktape
# exporter
decktape reveal http://localhost:1948 slides/output.pdf
```

Docker (optionnel)

- Utiliser une image Node avec `reveal-md` installé:

```bash
docker run --rm -p 1948:1948 -v "${PWD}:/slides" -w /slides node:18 bash -c "npm install -g reveal-md && reveal-md slides/slides.md --disable-auto-open --port 1948"
```

Conseils pratiques

- Pour personnaliser le thème: ajouter front-matter YAML en tête de `slides/slides.md` ou fournir un CSS via `--css`.
- Pour ajouter images/diagrammes: placer les assets dans `slides/assets/` et référencer par chemin relatif.
- Pour générer PDF haute qualité: utiliser `decktape` ou impression depuis Chrome en 16:9, activer `Print background graphics`.

Support

- Si vous voulez, je peux: générer le PDF statique, ajouter un thème Bootstrap, ou produire une version PowerPoint (.pptx) via pandoc.

</main>
    <footer class="footer">
        <p>&copy; <?= date("Y") ?> Ewen et Vins - Vente interdite aux mineurs. L'abus d'alcool est dangereux pour la santé.</p>
    </footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleDescription(btn) {
    const container = btn.parentElement;
    const shortDesc = container.querySelector('.short-desc');
    const fullDesc = container.querySelector('.full-desc');
    
    if (fullDesc.style.display === 'none') {
        shortDesc.style.display = 'none';
        fullDesc.style.display = 'block';
        btn.textContent = 'Voir moins';
    } else {
        shortDesc.style.display = 'block';
        fullDesc.style.display = 'none';
        btn.textContent = 'Voir plus';
    }
}

document.addEventListener('DOMContentLoaded', function() {

    // Ajout au panier
    document.querySelectorAll(".add-to-cart-form").forEach(form => {
        form.addEventListener("submit", function(e) {
            e.preventDefault();

            const id = this.dataset.id;
            const quantite = this.querySelector("input[name='quantite']").value;

            fetch("index.php?page=panier-ajax", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "id_produit=" + encodeURIComponent(id) + "&quantite=" + encodeURIComponent(quantite)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: "Produit ajouté !",
                        text: "Voulez-vous continuer vos achats ou aller au panier ?",
                        icon: "success",
                        showCancelButton: true,
                        confirmButtonText: "Aller au panier",
                        cancelButtonText: "Continuer"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "index.php?page=panier";
                        }
                    });
                }
            });
        });
    });

    // Mise à jour des quantités dans le panier (Version multi-types)
    document.addEventListener("change", function(e) {
        if (e.target.classList.contains("update-qty")) {
            const input = e.target;
            const id = input.dataset.id;
            const quantite = parseInt(input.value);

            if (quantite < 1) {
                input.value = 1;
                return;
            }

            // Récupérer les données du produit
            const ligneProduit = input.closest('tr');
            const prixUnitaire = parseFloat(ligneProduit.getAttribute('data-prix-unitaire'));
            const bouteillesParCarton = parseInt(ligneProduit.getAttribute('data-bouteilles-carton'));
            const typeProduit = ligneProduit.getAttribute('data-type');
            const categorie = ligneProduit.getAttribute('data-categorie');
            
            let nouveauSousTotal;
            let detailsQuantite;

            // Calcul selon le type de produit
            if (typeProduit === 'coffret') {
                // COFFRETS : prix unitaire direct
                nouveauSousTotal = prixUnitaire * quantite;
                detailsQuantite = quantite + " coffret" + (quantite > 1 ? 's' : '');
            }
            else if (typeProduit === 'vin_biere') {
                // VINS/BIERES : prix unitaire × bouteilles par carton × quantité
                const prixCarton = prixUnitaire * bouteillesParCarton;
                nouveauSousTotal = prixCarton * quantite;
                const totalBouteilles = bouteillesParCarton * quantite;
                detailsQuantite = quantite + " carton" + (quantite > 1 ? 's' : '') + " (" + totalBouteilles + " bouteille" + (totalBouteilles > 1 ? 's' : '') + ")";
            }
            else {
                // ACCESSOIRES : prix unitaire direct
                nouveauSousTotal = prixUnitaire * quantite;
                const totalPieces = quantite * bouteillesParCarton;
                if (bouteillesParCarton > 1) {
                    detailsQuantite = quantite + " lot" + (quantite > 1 ? 's' : '') + " (" + totalPieces + " pièce" + (totalPieces > 1 ? 's' : '') + ")";
                } else {
                    detailsQuantite = quantite + " pièce" + (quantite > 1 ? 's' : '');
                }
            }

            fetch("index.php?page=update-qty", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "id_produit=" + encodeURIComponent(id) + "&quantite=" + encodeURIComponent(quantite)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Met à jour le sous-total
                    input.closest("tr").querySelector(".sous-total").textContent = nouveauSousTotal.toFixed(2) + " €";
                    
                    // Met à jour les détails de quantité
                    const detailsQuantiteElement = ligneProduit.querySelector('.details-quantite');
                    if (detailsQuantiteElement) {
                        detailsQuantiteElement.textContent = detailsQuantite;
                    }
                    
                    // Met à jour le total général
                    mettreAJourTotalGeneral();
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }
    });

    // Suppression d'un produit du panier
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("remove-item")) {
            e.preventDefault();
            const btn = e.target;
            const id = btn.dataset.id;

            Swal.fire({
                title: "Supprimer ce produit ?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Oui",
                cancelButtonText: "Annuler"
            }).then((res) => {
                if (res.isConfirmed) {
                    fetch("index.php?page=remove-item", {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: "id_produit=" + encodeURIComponent(id)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Supprimer la ligne
                            btn.closest("tr").remove();
                            
                            // Mettre à jour le total général
                            mettreAJourTotalGeneral();
                            
                            // Vérifier si panier vide
                            if (document.querySelectorAll(".remove-item").length === 0) {
                                document.querySelector("table.panier-table").remove();
                                document.querySelector(".panier-resume").remove();
                                document.querySelector("h1").insertAdjacentHTML("afterend", "<p>Votre panier est vide.</p>");
                            }
                        }
                    });
                }
            });
        }
    });

    // Fonction pour mettre à jour le total général
    function mettreAJourTotalGeneral() {
        let total = 0;
        
        document.querySelectorAll('tr[data-id]').forEach(ligne => {
            const sousTotalText = ligne.querySelector('.sous-total').textContent;
            const sousTotal = parseFloat(sousTotalText.replace(' €', ''));
            if (!isNaN(sousTotal)) {
                total += sousTotal;
            }
        });
        
        const totalElement = document.getElementById('total-general');
        if (totalElement) {
            totalElement.textContent = total.toFixed(2);
        }
    }

});
</script>
</body>
</html>
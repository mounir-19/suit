 // Sample articles data for offer selection
        const articlesData = [
            {
                id: 1,
                name: "Costume Mariage Classic",
                price: 25000,
                image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23D4AE6A'/%3E%3Ctext x='20' y='25' text-anchor='middle' fill='%23fff' font-size='10'%3EPhoto%3C/text%3E%3C/svg%3E"
            },
            {
                id: 2,
                name: "Costume Bureau Élégant",
                price: 18500,
                image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23D4AE6A'/%3E%3Ctext x='20' y='25' text-anchor='middle' fill='%23fff' font-size='10'%3EPhoto%3C/text%3E%3C/svg%3E"
            },
            {
                id: 3,
                name: "Smoking Noir Premium",
                price: 35000,
                image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23D4AE6A'/%3E%3Ctext x='20' y='25' text-anchor='middle' fill='%23fff' font-size='10'%3EPhoto%3C/text%3E%3C/svg%3E"
            },
            {
                id: 4,
                name: "Veste Casual Moderne",
                price: 12500,
                image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23D4AE6A'/%3E%3Ctext x='20' y='25' text-anchor='middle' fill='%23fff' font-size='10'%3EPhoto%3C/text%3E%3C/svg%3E"
            }
        ];

        // Wait for DOM to load before executing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            
            // Mobile menu toggle
            const menuToggle = document.getElementById('menuToggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('active');
                });
            }

            // Navigation functionality
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all links
                    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Hide all sections
                    document.querySelectorAll('.admin-section').forEach(section => {
                        section.style.display = 'none';
                    });
                    
                    // Show selected section
                    const targetSection = this.getAttribute('href').substring(1) + '-section';
                    const targetElement = document.getElementById(targetSection);
                    if (targetElement) {
                        targetElement.style.display = 'block';
                    }
                });
            });

            // Populate articles when offer modal opens
            const offerModal = document.getElementById('offer-modal');
            const originalOpenModal = window.openModal;
            
            // Form submissions
            const articleForm = document.getElementById('article-form');
            if (articleForm) {
                articleForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Article enregistré avec succès!');
                    closeModal('article-modal');
                    // Add logic to save article
                });
            }

            const offerForm = document.getElementById('offer-form');
            if (offerForm) {
                offerForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const selectedArticles = Array.from(document.querySelectorAll('input[name="selected-articles"]:checked'));
                    if (selectedArticles.length === 0) {
                        alert('Veuillez sélectionner au moins un article pour cette offre.');
                        return;
                    }
                    alert('Offre enregistrée avec succès!');
                    closeModal('offer-modal');
                    // Add logic to save offer
                });
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            }
        });

        // Enhanced openModal function
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
                
                // If it's the offer modal, populate articles list
                if (modalId === 'offer-modal') {
                    populateArticlesList();
                }
            }
        }

        // Function to populate articles list in offer modal
        function populateArticlesList() {
            const articlesList = document.getElementById('articles-list');
            if (!articlesList) return;

            articlesList.innerHTML = '';
            
            articlesData.forEach(article => {
                const articleDiv = document.createElement('div');
                articleDiv.className = 'article-checkbox';
                articleDiv.innerHTML = `
                    <input type="checkbox" name="selected-articles" value="${article.id}" onchange="updateSelectedArticles()">
                    <div class="article-info">
                        <img src="${article.image}" alt="${article.name}">
                        <div class="article-details">
                            <div class="article-name">${article.name}</div>
                            <div class="article-price">${article.price.toLocaleString()} DA</div>
                        </div>
                    </div>
                `;
                articlesList.appendChild(articleDiv);
            });
        }

        // Function to update selected articles display
        function updateSelectedArticles() {
            const selectedCheckboxes = document.querySelectorAll('input[name="selected-articles"]:checked');
            const selectedArticlesDiv = document.getElementById('selected-articles');
            const selectedArticlesList = document.getElementById('selected-articles-list');
            const selectedCount = document.getElementById('selected-count');
            
            if (selectedCheckboxes.length > 0) {
                selectedArticlesDiv.style.display = 'block';
                selectedArticlesList.innerHTML = '';
                
                selectedCheckboxes.forEach(checkbox => {
                    const articleId = parseInt(checkbox.value);
                    const article = articlesData.find(a => a.id === articleId);
                    if (article) {
                        const articleSpan = document.createElement('span');
                        articleSpan.textContent = article.name;
                        articleSpan.style.display = 'inline-block';
                        articleSpan.style.margin = '2px 5px';
                        articleSpan.style.padding = '2px 8px';
                        articleSpan.style.backgroundColor = '#D4AE6A';
                        articleSpan.style.color = 'white';
                        articleSpan.style.borderRadius = '12px';
                        articleSpan.style.fontSize = '0.8rem';
                        selectedArticlesList.appendChild(articleSpan);
                    }
                });
                
                selectedCount.textContent = `${selectedCheckboxes.length} article(s) sélectionné(s)`;
            } else {
                selectedArticlesDiv.style.display = 'none';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Article functions
        function editArticle(id) {
            openModal('article-modal');
            // Populate form with article data (would fetch from database in real app)
        }

        function deleteArticle(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
                // Delete article logic here
                alert('Article supprimé avec succès!');
            }
        }

        // Order functions
        function updateOrderStatus(orderId, newStatus) {
            const statusMap = {
                'confirmed': 'Confirmée',
                'shipped': 'Expédiée',
                'delivered': 'Livrée'
            };
            
            if (confirm(`Changer le statut de la commande ${orderId} à "${statusMap[newStatus]}" ?`)) {
                // Update order status logic here
                alert('Statut mis à jour avec succès!');
                location.reload();
            }
        }

        function viewOrderDetails(orderId) {
            const orderDetails = `
                <div style="font-family: Poppins, sans-serif;">
                    <h3>Commande ${orderId}</h3>
                    <p><strong>Client:</strong> ${orderId === 'CMD001' ? 'Ahmed Benali' : orderId === 'CMD002' ? 'Fatima Khediri' : 'Mohamed Larbi'}</p>
                    <p><strong>Téléphone:</strong> +213 555 123 456</p>
                    <p><strong>Adresse:</strong> Alger, Algérie</p>
                    <hr>
                    <h4>Articles commandés:</h4>
                    <ul>
                        <li>Costume Mariage Classic - 1x - 25,000 DA</li>
                    </ul>
                    <hr>
                    <p><strong>Total:</strong> 25,000 DA</p>
                    <p><strong>Mode de paiement:</strong> Paiement à la livraison</p>
                </div>
            `;
            const orderDetailsContent = document.getElementById('order-details-content');
            if (orderDetailsContent) {
                orderDetailsContent.innerHTML = orderDetails;
            }
            openModal('order-modal');
        }

        // Offer functions
        function editOffer(id) {
            openModal('offer-modal');
            // Populate form with offer data
        }

        function deleteOffer(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette offre ?')) {
                // Delete offer logic here
                alert('Offre supprimée avec succès!');
            }
        }

        // Auto-update dashboard stats (simulated)
        function updateDashboardStats() {
            // Simulate real-time updates
            const stats = {
                articles: Math.floor(Math.random() * 10) + 20,
                orders: Math.floor(Math.random() * 5) + 5,
                offers: Math.floor(Math.random() * 3) + 2,
                sales: (Math.floor(Math.random() * 5000) + 12000).toLocaleString()
            };
            
            document.getElementById('total-articles').textContent = stats.articles;
            document.getElementById('pending-orders').textContent = stats.orders;
            document.getElementById('active-offers').textContent = stats.offers;
            document.getElementById('monthly-sales').textContent = stats.sales + ' DA';
        }

        // Update stats every 30 seconds
        setInterval(updateDashboardStats, 30000);
        // Handle scrollable main content layout
function setScrollableLayout() {
    const header = document.querySelector('.header');
    const bar = document.querySelector('.bar');
    const adminContainer = document.querySelector('.admin-container');

    if (header && bar && adminContainer) {
        const totalOffset = header.offsetHeight + bar.offsetHeight;
        const availableHeight = window.innerHeight - totalOffset;
        adminContainer.style.height = availableHeight + 'px';
        adminContainer.style.overflowY = 'auto';
    }
}

window.addEventListener('load', setScrollableLayout);
window.addEventListener('resize', setScrollableLayout);

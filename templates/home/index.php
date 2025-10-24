<?php
/**
 * Home Page Template - PHP version
 */

$content = '
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Hero Section -->
        <div class="hero-section text-center py-5 mb-5">
            <div class="hero-background">
                <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="hero-wave">
                    <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
                </svg>
            </div>
            
            <div class="hero-content">
                <h1 class="display-4 fw-bold text-primary mb-4">
                    Welcome to ' . APP_NAME . '
                </h1>
                <p class="lead text-muted mb-4">
                    Streamline your support workflow with our intuitive ticket management system. 
                    Track issues, collaborate with your team, and deliver exceptional customer service.
                </p>
                <div class="hero-buttons">
                    <a href="/register" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-person-plus me-2"></i>Get Started
                    </a>
                    <a href="/login" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </a>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="feature-card h-100 p-4 border rounded-3 shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-ticket-perforated text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Easy Ticket Management</h4>
                    <p class="text-muted">
                        Create, assign, and track tickets with our user-friendly interface. 
                        Never lose track of customer issues again.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card h-100 p-4 border rounded-3 shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-graph-up text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Real-time Analytics</h4>
                    <p class="text-muted">
                        Monitor your support performance with interactive dashboards and 
                        detailed reporting tools.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card h-100 p-4 border rounded-3 shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-people text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Team Collaboration</h4>
                    <p class="text-muted">
                        Work together seamlessly with comments, assignments, and 
                        internal notes on every ticket.
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card text-center p-4 bg-primary text-white rounded-3">
                    <i class="bi bi-lightning-charge mb-2" style="font-size: 2rem;"></i>
                    <h3 class="fw-bold mb-1">Fast</h3>
                    <p class="mb-0">Lightning quick response times</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center p-4 bg-success text-white rounded-3">
                    <i class="bi bi-shield-check mb-2" style="font-size: 2rem;"></i>
                    <h3 class="fw-bold mb-1">Secure</h3>
                    <p class="mb-0">Enterprise-grade security</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center p-4 bg-info text-white rounded-3">
                    <i class="bi bi-gear mb-2" style="font-size: 2rem;"></i>
                    <h3 class="fw-bold mb-1">Reliable</h3>
                    <p class="mb-0">99.9% uptime guarantee</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center p-4 bg-warning text-white rounded-3">
                    <i class="bi bi-heart mb-2" style="font-size: 2rem;"></i>
                    <h3 class="fw-bold mb-1">Friendly</h3>
                    <p class="mb-0">Built with love for users</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section text-center py-5 bg-light rounded-3">
            <h2 class="fw-bold mb-3">Ready to get started?</h2>
            <p class="text-muted mb-4">
                Join thousands of teams already using ' . APP_NAME . ' to improve their support workflow.
            </p>
            <a href="/register" class="btn btn-primary btn-lg">
                <i class="bi bi-rocket-takeoff me-2"></i>Start Your Free Trial
            </a>
        </div>
    </div>
</div>

<style>
.hero-section {
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 1rem;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100px;
    fill: rgba(255, 255, 255, 0.1);
}

.hero-content {
    position: relative;
    z-index: 2;
    padding: 2rem;
}

.feature-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.stat-card {
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: scale(1.05);
}

.cta-section {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.cta-section h2,
.cta-section p {
    color: white;
}
</style>';

// Include the base template
include TEMPLATES_PATH . '/base.php';
?>

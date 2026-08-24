<?php
/**
 * Global Footer Component
 * Fixed Navigation Links & Professional Disclaimers
 */
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand" style="display: flex; align-items: center; gap: 8px; font-size: 1.3rem; font-weight: 800; color: #ffffff; margin-bottom: 12px;">
                    <span>🎓</span> Scholar<span style="color: var(--primary);">Find</span>
                </div>
                <p style="font-size: 0.88rem; line-height: 1.6; color: #94a3b8; margin-bottom: 16px;">
                    Empowering students across India with verified scholarship discovery, instant eligibility calculation, and centralized deadline tracking.
                </p>
                <div style="font-size: 0.78rem; color: #64748b; line-height: 1.5;">
                    🛡️ Verified Central, State Government (Maharashtra, Karnataka, UP, WB, etc.) & Leading Corporate Foundations.
                </div>
            </div>
            
            <div>
                <h4 style="color: #ffffff; margin-bottom: 14px; font-size: 1rem; font-weight: 700;">Platform Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="scholarships.php">Find Scholarships</a></li>
                    <li><a href="eligibility_checker.php">Smart Match Calculator</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="dashboard.php">Student Dashboard</a></li>
                        <li><a href="saved.php">Saved Bookmarks</a></li>
                        <li><a href="profile.php">My Profile Settings</a></li>
                    <?php else: ?>
                        <li><a href="register.php">Create Free Account</a></li>
                        <li><a href="login.php">Student Sign In</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div>
                <h4 style="color: #ffffff; margin-bottom: 14px; font-size: 1rem; font-weight: 700;">Explore Categories</h4>
                <ul class="footer-links">
                    <li><a href="scholarships.php?category=General+%2F+Open">General / Open Merit</a></li>
                    <li><a href="scholarships.php?category=OBC">OBC Scholarships</a></li>
                    <li><a href="scholarships.php?category=SC">SC / ST Financial Aid</a></li>
                    <li><a href="scholarships.php?category=VJ%2FNT">VJ/NT & SBC Schemes</a></li>
                    <li><a href="scholarships.php?state=Maharashtra">Maharashtra Schemes</a></li>
                    <li><a href="scholarships.php?search=Tata">Tata Scholarships</a></li>
                    <li><a href="scholarships.php?search=MahaDBT">MahaDBT Schemes</a></li>
                    <li><a href="scholarships.php?gender=female">Women-Focused Scholarships</a></li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: #ffffff; margin-bottom: 14px; font-size: 1rem; font-weight: 700;">Official Advisory</h4>
                <p style="font-size: 0.82rem; line-height: 1.6; color: #94a3b8; margin-bottom: 14px;">
                    ScholarFind is an independent discovery platform. Applications are submitted and processed directly on authorized official portals.
                </p>
                <a href="scholarships.php" class="btn btn-sm btn-outline" style="border-color: #475569; color: #ffffff !important; display: inline-block;">
                    🔍 Browse All 35+ Schemes
                </a>
            </div>
        </div>
        
        <div class="footer-bottom" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 24px; margin-top: 36px; font-size: 0.84rem; color: #64748b;">
            <div>
                &copy; <?= date('Y') ?> <strong>ScholarFind</strong> – Scholarship Finder & Eligibility Checker. Built for student success.
            </div>
            <div>
                <span>Find. Check. Achieve.</span>
            </div>
        </div>
    </div>
</footer>

<script src="assets/js/main.js"></script>
<?php if (isset($extra_scripts)): ?>
    <?= $extra_scripts ?>
<?php endif; ?>
</body>
</html>

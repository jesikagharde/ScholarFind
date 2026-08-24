/**
 * Interactive Eligibility Calculator JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // Slider value bindings
    const pctSlider = document.getElementById('calc-percentage');
    const pctVal = document.getElementById('pct-val');
    if (pctSlider && pctVal) {
        pctSlider.addEventListener('input', () => {
            pctVal.innerText = `${pctSlider.value}%`;
        });
    }

    const incSlider = document.getElementById('calc-income');
    const incVal = document.getElementById('inc-val');
    if (incSlider && incVal) {
        incSlider.addEventListener('input', () => {
            const val = parseInt(incSlider.value);
            incVal.innerText = `₹${(val / 100000).toFixed(1)} Lakhs`;
        });
    }

    // Eligibility form submission handler
    const calcForm = document.getElementById('eligibility-calc-form');
    const resultsContainer = document.getElementById('eligibility-results-container');

    if (calcForm && resultsContainer) {
        calcForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            resultsContainer.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 1.2rem; color: var(--primary); font-weight: 600;">Analyzing your criteria against available scholarships...</div>
                </div>
            `;

            const formData = {
                education_level: document.getElementById('calc-education').value,
                percentage: parseFloat(document.getElementById('calc-percentage').value),
                family_income: parseFloat(document.getElementById('calc-income').value),
                category: document.getElementById('calc-category').value,
                state: document.getElementById('calc-state').value,
                gender: document.getElementById('calc-gender').value,
            };

            try {
                const res = await fetch('api/check_eligibility.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                const data = await res.json();

                if (!data.success || data.scholarships.length === 0) {
                    resultsContainer.innerHTML = `
                        <div class="alert alert-info">No scholarships matched these criteria. Try adjusting your parameters.</div>
                    `;
                    return;
                }

                renderResults(data.scholarships, resultsContainer);
            } catch (err) {
                console.error(err);
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger">An error occurred while calculating eligibility. Please try again.</div>
                `;
            }
        });
    }
});

function renderResults(scholarships, container) {
    let html = `
        <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.4rem; font-weight: 700;">Matched Scholarships (${scholarships.length} Results)</h3>
            <span style="font-size: 0.9rem; color: var(--muted);">Sorted by Highest Match Score</span>
        </div>
        <div class="scholarship-grid">
    `;

    scholarships.forEach(item => {
        const sch = item.scholarship;
        const elig = item.eligibility;

        let failedList = '';
        if (elig.failed_details && elig.failed_details.length > 0) {
            failedList = `
                <div style="margin: 12px 0; font-size: 0.85rem; color: #b91c1c; background: #fef2f2; padding: 10px; border-radius: 6px;">
                    <strong>Requirement Note:</strong>
                    <ul style="margin-left: 18px; margin-top: 4px;">
                        ${elig.failed_details.map(f => `<li>${f}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        html += `
            <div class="scholarship-card">
                <div>
                    <div class="card-header">
                        <span class="card-provider">${escapeHtml(sch.provider)}</span>
                        <span class="badge ${elig.badge_class}">${elig.status_label}</span>
                    </div>
                    <h4 class="card-title">${escapeHtml(sch.title)}</h4>
                    <div class="card-amount">₹${Number(sch.amount).toLocaleString('en-IN')} <span style="font-size: 0.85rem; color: var(--muted); font-weight: normal;">/ year</span></div>
                    
                    <div class="card-meta">
                        <span class="meta-tag">🎓 ${escapeHtml(sch.education_level)}</span>
                        <span class="meta-tag">🏷️ ${escapeHtml(sch.category)}</span>
                        <span class="meta-tag">📍 ${escapeHtml(sch.state)}</span>
                    </div>

                    ${failedList}
                </div>

                <div class="card-footer">
                    <span class="deadline-text">📅 Deadline: ${sch.deadline}</span>
                    <a href="scholarship_detail.php?id=${sch.scholarship_id}" class="btn btn-sm btn-primary">View Details →</a>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    container.innerHTML = html;
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

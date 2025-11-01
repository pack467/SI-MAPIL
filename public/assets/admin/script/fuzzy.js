// =====================================
// Cek Fuzzy – ambil identitas & hitung
// =====================================
document.addEventListener('DOMContentLoaded', () => {
  const wrap = document.querySelector('.content-wrapper');
  const MAHASISWA_ID = wrap.dataset.mahasiswaId;
  const API_HITUNG = wrap.dataset.apiHitung;
  const API_IDENT = wrap.dataset.apiIdentitas;

  const loading = document.getElementById('loadingOverlay');
  const showLoading = (m='Memuat data...') => { 
    loading.querySelector('p').textContent = m; 
    loading.style.display='flex'; 
  };
  const hideLoading = () => loading.style.display='none';
  const unhide = ids => ids.forEach(id => document.getElementById(id)?.classList.remove('hidden'));

  // Simpan data response untuk digunakan di modal
  let responseData = null;

  // Load identitas mahasiswa
  initIdent();

  async function initIdent(){
    try {
      const res = await fetch(`${API_IDENT}?mahasiswa_id=${encodeURIComponent(MAHASISWA_ID)}`);
      const m = await res.json();
      setText('mhs-nama', m.nama);
      setText('mhs-nim', m.nim);
      setText('mhs-semester', m.semester);
    } catch(err) {
      console.error('Gagal memuat identitas:', err);
    }
  }

  // Tombol hitung
  document.getElementById('checkCompatibilityBtn').addEventListener('click', async ()=>{
    showLoading('Menghitung Fuzzy Tsukamoto...');
    try {
      const res = await fetch(API_HITUNG, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ mahasiswa_id: MAHASISWA_ID })
      });
      const json = await res.json();
      hideLoading();

      // Simpan response untuk digunakan nanti
      responseData = json;

      // Render semua section
      renderSummary(json.summary || {});
      unhide(['courseSummarySection','gradeExplanationSection']);

      // Fuzzifikasi - tampilkan semua kriteria
      renderFuzzification(json.fuzzifikasi || {}, json.summary || {});

      renderRules(json.rules || []);
      renderInference(json.inferensi || []);
      renderDefuzz(json.defuzz || []);
      renderResult(json.result || {detail:[], rekomendasi:'-'});
      unhide(['fuzzificationSection','rulesSection','inferenceSection','defuzzificationSection','resultSection','actionButtonsSection']);
    } catch(err) {
      hideLoading();
      alert('Gagal menghitung fuzzy: ' + err.message);
      console.error(err);
    }
  });

  // Tombol aksi
  document.getElementById('exitBtn').addEventListener('click', ()=>history.back());
  document.getElementById('refreshBtn').addEventListener('click', ()=>location.reload());
  document.getElementById('printBtn').addEventListener('click', ()=>window.print());
  document.getElementById('closeModal').addEventListener('click', closeModal);

  // Close modal when clicking outside
  document.getElementById('detailModal').addEventListener('click', (e) => {
    if (e.target.id === 'detailModal') {
      closeModal();
    }
  });

  function closeModal() {
    document.getElementById('detailModal').classList.remove('show');
  }

  // ==== RENDERERS ====
  
  function renderSummary(sum){
    const body = document.getElementById('summaryBody');
    body.innerHTML = '';
    const rows = [
      ['Robotika', sum.robotika],
      ['Matematika', sum.matematika],
      ['Pemrograman', sum.pemrograman],
      ['Analisis', sum.analisis],
    ];
    rows.forEach(([label, val])=>{
      const id = label.toLowerCase();
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${label}</td>
        <td>${Number(val||0).toFixed(2)}</td>
        <td><button class="btn btn-sm btn-outline view-detail-btn" data-field="${id}" data-value="${val||0}">
          <i class="fas fa-eye"></i> Cek Detail
        </button></td>`;
      body.appendChild(tr);
    });

    // Event listener untuk button Cek Detail - BUKA MODAL
    body.querySelectorAll('.view-detail-btn').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const field = btn.dataset.field;
        showDetailModal(field);
      });
    });
  }

  /**
   * Tampilkan modal dengan detail perhitungan nilai matakuliah
   */
  function showDetailModal(field) {
    if (!responseData || !responseData.detail_nilai) {
      alert('Data detail tidak tersedia');
      return;
    }

    const fieldName = field.charAt(0).toUpperCase() + field.slice(1);
    const detailData = responseData.detail_nilai[field] || [];
    const nilaiTotal = responseData.summary[field] || 0;

    // Set modal title
    document.getElementById('modalTitle').textContent = `Detail Nilai ${fieldName}`;

    // Build modal content
    let html = `
      <div class="detail-header">
        <h4>Kriteria: ${fieldName}</h4>
        <p><strong>Nilai Total (Skala 0-100):</strong> ${Number(nilaiTotal).toFixed(2)}</p>
      </div>
    `;

    if (detailData.length === 0) {
      html += '<p>Tidak ada data nilai untuk kriteria ini.</p>';
    } else {
      html += `
        <div class="detail-table-container">
          <table class="detail-table">
            <thead>
              <tr>
                <th>Mata Kuliah</th>
                <th>Semester</th>
                <th>Nilai Huruf</th>
                <th>Nilai Angka</th>
              </tr>
            </thead>
            <tbody>
      `;

      let totalAngka = 0;
      detailData.forEach(item => {
        totalAngka += Number(item.angka || 0);
        html += `
          <tr>
            <td>${item.matakuliah}</td>
            <td>${item.semester}</td>
            <td><strong>${item.huruf}</strong></td>
            <td>${Number(item.angka).toFixed(2)}</td>
          </tr>
        `;
      });

      const rataRata = detailData.length > 0 ? totalAngka / detailData.length : 0;
      const nilaiSkala100 = rataRata * 25;

      html += `
            </tbody>
          </table>
        </div>
        <div class="calculation-explanation">
          <h4>Penjelasan Perhitungan:</h4>
          <p><strong>Total Mata Kuliah:</strong> ${detailData.length}</p>
          <p><strong>Jumlah Nilai Angka:</strong> ${totalAngka.toFixed(2)}</p>
          <p><strong>Rata-rata (Skala 0-4):</strong> ${rataRata.toFixed(2)}</p>
          <p><strong>Konversi ke Skala 0-100:</strong> ${rataRata.toFixed(2)} × 25 = <strong>${nilaiSkala100.toFixed(2)}</strong></p>
        </div>
      `;
    }

    document.getElementById('modalDetailContent').innerHTML = html;
    document.getElementById('detailModal').classList.add('show');
  }

  /**
   * Render fuzzifikasi dengan tabel membership dan perhitungan detail
   */
  function renderFuzzification(fuzzyData, summary) {
    const wrap = document.getElementById('fuzzyFields');
    wrap.innerHTML = '';

    ['robotika', 'matematika', 'pemrograman', 'analisis'].forEach(field => {
      if (!fuzzyData[field]) return;

      const cap = field.charAt(0).toUpperCase() + field.slice(1);
      const value = summary[field] || 0;
      const μ = fuzzyData[field];

      // Hitung perhitungan untuk setiap himpunan
      const calculations = {
        lemah: calculateTrapezoid(value, 0, 0, 20, 40),
        lumayan: calculateTrapezoid(value, 20, 40, 60, 80),
        kuat: calculateTrapezoid(value, 60, 80, 100, 100)
      };

      const block = document.createElement('div');
      block.className = 'fuzzification-field';
      block.dataset.field = field;
      block.innerHTML = `
        <div class="field-header">
          <h3>${cap}: <span class="score">${Number(value).toFixed(2)}</span></h3>
          <button class="btn btn-sm btn-outline toggle-chart-btn">
            <i class="fas fa-chart-line"></i> <span class="btn-text">Lihat Grafik</span>
          </button>
        </div>
        <div class="fuzzification-table-container">
          <table class="fuzzification-table">
            <thead>
              <tr>
                <th>Himpunan</th>
                <th>Parameter</th>
                <th>Perhitungan</th>
                <th>Hasil</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Lemah</strong></td>
                <td>0, 0, 20, 40</td>
                <td>${calculations.lemah.formula}</td>
                <td><span class="membership-value ${μ.lemah > 0 ? 'active' : ''}">${Number(μ.lemah).toFixed(2)}</span></td>
              </tr>
              <tr>
                <td><strong>Lumayan</strong></td>
                <td>20, 40, 60, 80</td>
                <td>${calculations.lumayan.formula}</td>
                <td><span class="membership-value ${μ.lumayan > 0 ? 'active' : ''}">${Number(μ.lumayan).toFixed(2)}</span></td>
              </tr>
              <tr>
                <td><strong>Kuat</strong></td>
                <td>60, 80, 100, 100</td>
                <td>${calculations.kuat.formula}</td>
                <td><span class="membership-value ${μ.kuat > 0 ? 'active' : ''}">${Number(μ.kuat).toFixed(2)}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="chart-container hidden" id="chart-${field}">
          <canvas id="canvas-${field}"></canvas>
        </div>
      `;
      wrap.appendChild(block);

      // Toggle chart visibility
      const toggleBtn = block.querySelector('.toggle-chart-btn');
      toggleBtn.addEventListener('click', ()=>{
        const c = document.getElementById(`chart-${field}`);
        const btnText = toggleBtn.querySelector('.btn-text');
        if(c.classList.contains('hidden')){
          c.classList.remove('hidden');
          btnText.textContent = 'Sembunyikan Grafik';
          renderChart(field, Number(value), μ);
        } else {
          c.classList.add('hidden');
          btnText.textContent = 'Lihat Grafik';
          if(charts[field]){ 
            charts[field].destroy(); 
            delete charts[field]; 
          }
        }
      });
    });
  }

  /**
   * Fungsi untuk menghitung membership trapezoid dengan formula
   */
  function calculateTrapezoid(x, a, b, c, d) {
    let result = 0;
    let formula = '';

    if (x <= a || x >= d) {
      formula = '0 (Nilai di luar Range)';
      result = 0;
    } else if (x >= b && x <= c) {
      formula = '1 (Nilai di plateau)';
      result = 1;
    } else if (x > a && x < b) {
      const numerator = (x - a).toFixed(2);
      const denominator = (b - a).toFixed(2);
      const calc = ((x - a) / (b - a)).toFixed(2);
      formula = `(${x}-${a})/(${b}-${a}) = ${numerator}/${denominator} = ${calc}`;
      result = (x - a) / (b - a);
    } else if (x > c && x < d) {
      const numerator = (d - x).toFixed(2);
      const denominator = (d - c).toFixed(2);
      const calc = ((d - x) / (d - c)).toFixed(2);
      formula = `(${d}-${x})/(${d}-${c}) = ${numerator}/${denominator} = ${calc}`;
      result = (d - x) / (d - c);
    }

    return { result, formula };
  }

  const charts = {};

  /**
   * Render grafik fungsi keanggotaan menggunakan Chart.js
   */
  function renderChart(field, value, μ){
    const ctx = document.getElementById(`canvas-${field}`).getContext('2d');
    
    // Destroy existing chart
    if(charts[field]) {
      charts[field].destroy();
    }
    
    charts[field] = new Chart(ctx, {
      type: 'line',
      data: {
        datasets: [
          {
            label: 'Lemah',
            data: [
              {x: 0, y: 1},
              {x: 20, y: 1},
              {x: 40, y: 0}
            ],
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            fill: true,
            borderWidth: 2,
            tension: 0,
            pointRadius: 4,
            pointHoverRadius: 6
          },
          {
            label: 'Lumayan',
            data: [
              {x: 20, y: 0},
              {x: 40, y: 1},
              {x: 60, y: 1},
              {x: 80, y: 0}
            ],
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            fill: true,
            borderWidth: 2,
            tension: 0,
            pointRadius: 4,
            pointHoverRadius: 6
          },
          {
            label: 'Kuat',
            data: [
              {x: 60, y: 0},
              {x: 80, y: 1},
              {x: 100, y: 1}
            ],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            borderWidth: 2,
            tension: 0,
            pointRadius: 4,
            pointHoverRadius: 6
          },
          {
            label: `Nilai Aktual (${value.toFixed(2)})`,
            data: [
              {x: value, y: 0},
              {x: value, y: Math.max(μ.lemah, μ.lumayan, μ.kuat)}
            ],
            borderColor: '#3b82f6',
            backgroundColor: '#3b82f6',
            borderWidth: 3,
            borderDash: [8, 4],
            pointRadius: 5,
            pointStyle: 'circle',
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        plugins: {
          title: {
            display: true,
            text: `Grafik Fuzzifikasi ${field.charAt(0).toUpperCase() + field.slice(1)}`,
            font: { size: 16, weight: 'bold' },
            padding: { top: 10, bottom: 20 }
          },
          legend: {
            display: true,
            position: 'top',
            labels: { 
              usePointStyle: true,
              padding: 15,
              font: { size: 12 }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${context.dataset.label}: μ = ${context.parsed.y.toFixed(3)}`;
              }
            }
          }
        },
        scales: {
          x: {
            type: 'linear',
            min: 0,
            max: 100,
            ticks: { 
              stepSize: 10,
              font: { size: 11 }
            },
            title: {
              display: true,
              text: 'Nilai (0-100)',
              font: { size: 13, weight: 'bold' }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          y: {
            min: 0,
            max: 1.1,
            ticks: { 
              stepSize: 0.1,
              font: { size: 11 }
            },
            title: {
              display: true,
              text: 'Derajat Keanggotaan (μ)',
              font: { size: 13, weight: 'bold' }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          }
        }
      }
    });
  }

  /**
   * Render tabel aturan fuzzy yang aktif
   */
  function renderRules(rules){
    const body = document.getElementById('rulesBody');
    body.innerHTML = '';
    
    rules.forEach((r, i)=>{
      const level = r.alpha >= 0.66 ? 'high' : (r.alpha >= 0.33 ? 'medium' : 'low');
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><span class="rule-id">${r.id ?? ('R' + (i+1))}</span></td>
        <td class="condition-cell">
          <div class="condition-text"><strong>JIKA</strong> ${r.kondisi_html}</div>
        </td>
        <td class="output-cell">
          <div class="output-text"><strong>MAKA</strong> ${r.output_html}</div>
        </td>
        <td class="alpha-cell">
          <span class="alpha-badge ${level}">α = ${Number(r.alpha).toFixed(2)}</span>
        </td>`;
      body.appendChild(tr);
    });
  }

  /**
   * Render detail inferensi per rule
   */
  function renderInference(inf){
    const box = document.getElementById('inferenceContainer');
    box.innerHTML = '';
    
    inf.forEach(item=>{
      const el = document.createElement('div');
      el.className = 'inference-item';
      el.innerHTML = `
        <div class="inference-header">
          <h4><span class="rule-badge">${item.rule}</span> ${item.label}</h4>
        </div>
        ${item.steps.map(s=>`<div class="calculation-step">${s}</div>`).join('')}`;
      box.appendChild(el);
    });
  }

  /**
   * Render hasil defuzzifikasi dengan format tabel yang rapi
   */
  function renderDefuzz(cards){
    const wrap = document.getElementById('defuzzCards');
    wrap.innerHTML = '';
    
    cards.forEach(c=>{
      const d = document.createElement('div');
      d.className = 'defuzzification-card';
      
      // Build table rows HTML
      let tableHTML = `
        <table class="defuzz-table">
          <thead>
            <tr>
              <th>Rule</th>
              <th>α</th>
              <th>z</th>
              <th>α × z</th>
            </tr>
          </thead>
          <tbody>
      `;
      
      c.table_rows.forEach(row => {
        tableHTML += `
          <tr>
            <td><strong>${row.rule_id}</strong></td>
            <td>${row.alpha}</td>
            <td>${row.z}</td>
            <td><span class="calc-result">${row.result}</span></td>
          </tr>
        `;
      });
      
      tableHTML += `
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td colspan="3"><strong>Σ(α × z)</strong></td>
              <td><strong class="calc-result">${c.sum_az}</strong></td>
            </tr>
          </tfoot>
        </table>
      `;
      
      // Build alpha sum
      const alphaSumHTML = `
        <div class="alpha-sum-section">
          <div class="alpha-sum-label"><strong>Σα</strong> =</div>
          <div class="alpha-sum-values">${c.alpha_values.join(' + ')}</div>
          <div class="alpha-sum-result">= <strong class="calc-result">${c.sum_alpha}</strong></div>
        </div>
      `;
      
      // Build final calculation
      const finalCalcHTML = `
        <div class="final-calc-section">
          <div class="final-calc-formula">
            <strong>Hasil Defuzzifikasi:</strong>
            <div class="formula-line">
              z* = Σ(α × z) ÷ Σα
            </div>
            <div class="formula-line">
              z* = ${c.sum_az} ÷ ${c.sum_alpha} = <span class="calc-result">${Number(c.score).toFixed(2)}</span>
            </div>
          </div>
        </div>
      `;
      
      d.innerHTML = `
        <div class="card-header">
          <h3><i class="${c.icon}"></i> ${c.title}</h3>
        </div>
        <div class="card-body">
          <div class="defuzz-header-label">Rules yang aktif untuk ${c.title}:</div>
          ${tableHTML}
          ${alphaSumHTML}
          ${finalCalcHTML}
        </div>
        <div class="defuzzification-result">
          <div class="result-value">${Number(c.score).toFixed(2)}</div>
          <div class="result-label">${c.label}</div>
        </div>`;
      wrap.appendChild(d);
    });
  }

  /**
   * Render hasil akhir rekomendasi
   */
  function renderResult(res){
    const card = document.getElementById('resultCard');
    card.innerHTML = `
      <div class="result-icon"><i class="fas fa-award"></i></div>
      <div class="result-content">
        <h3>Hasil Kecocokan Mata Kuliah Peminatan</h3>
        <div class="result-scores">
          ${res.detail.map(d => {
            const isMax = d.nilai === Math.max(...res.detail.map(x => x.nilai));
            return `
              <div class="result-score ${isMax ? 'max-score' : ''}">
                <span class="score-type">${d.nama}:</span>
                <span class="score-value">${Number(d.nilai).toFixed(2)}</span>
                ${isMax ? '<i class="fas fa-star"></i>' : ''}
              </div>`;
          }).join('')}
        </div>
        <div class="result-recommendation">
          <strong>Rekomendasi Mata Kuliah:</strong>
          <div class="recommendation-badge">${res.rekomendasi}</div>
        </div>
      </div>`;
  }

  /**
   * Helper: set text content
   */
  function setText(id, txt){ 
    const el = document.getElementById(id); 
    if(el) el.textContent = txt ?? '-'; 
  }
});
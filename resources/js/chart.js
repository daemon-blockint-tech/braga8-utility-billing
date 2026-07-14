export function initChart() {
    const chartElement = document.getElementById("chart");
    if (!chartElement) return;

    const paid = parseFloat(chartElement.dataset.paid) || 0;
    const unpaid = parseFloat(chartElement.dataset.unpaid) || 0;
    const overdue = parseFloat(chartElement.dataset.overdue) || 0;

    chartElement.style.background = `
        conic-gradient(
            #ff8a2a 0% ${paid}%, 
            #d65a0f ${paid}% ${paid + unpaid}%, 
            #8b3a0e ${paid + unpaid}% 100%
        )
    `;

    if(document.getElementById("paid-val")) document.getElementById("paid-val").innerText = paid + "%";
    if(document.getElementById("unpaid-val")) document.getElementById("unpaid-val").innerText = unpaid + "%";
    if(document.getElementById("overdue-val")) document.getElementById("overdue-val").innerText = overdue + "%";
}
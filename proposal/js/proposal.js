document.addEventListener("DOMContentLoaded", function () {
    // === Date and Time Pickers ===
    flatpickr("input[name='date_range']", {
        mode: "range",
        dateFormat: "m/d/Y",
        minDate: "today",
        onDayCreate: function (dObj, dStr, fp, dayElem) {
            highlightProposalDates(dayElem.dateObj, dayElem);
        },
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                const [start, end] = selectedDates;
                let conflict = false;
                for (let d = new Date(start); d <= end;) {
                    if (isDateInDisabledRanges(d)) {
                        conflict = true;
                        break;
                    }
                    d = new Date(d.getTime() + 86400000); // add 1 day
                }
                if (conflict) {
                    alert("Already have proposal on selected date range. Please choose another date.");
                    instance.clear();
                }
            }
        },
        disable: disabledRanges.map(r => ({
            from: r.from,
            to: r.to
        }))
    });

    flatpickr("#startTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K"
    });

    flatpickr("#endTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K"
    });

    // === Time Combination on Submit ===
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", function () {
            const start = document.getElementById("startTime").value;
            const end = document.getElementById("endTime").value;
            document.getElementById("combinedTime").value = start + " - " + end;
        });
    }

    // === Calendar Iframe Load ===
    const calendarFrame = document.getElementById("calendarFrame");
    if (calendarFrame) {
        calendarFrame.src = "../proposal/calendar.php";
    }

    // === Disable form if budget is approved ===
    if (typeof budgetApproved !== 'undefined' && budgetApproved) {
        const inputs = document.querySelectorAll("form input, form select, form textarea");
        inputs.forEach(el => el.disabled = true);
    }

    // === Notifications ===
    const notifIcon = document.getElementById('notificationIcon');
    const notifCountBadge = document.getElementById('notificationCount');
    const notifContainer = document.getElementById('notificationContainer');
    const notifList = document.getElementById('notificationList');
    const closeNotifBtn = document.getElementById('closeNotifBtn');

    if (notifIcon && notifCountBadge && notifContainer && notifList && closeNotifBtn) {
        if (notifCount > 0) {
            notifIcon.style.display = 'block';
            notifCountBadge.textContent = notifCount;
        } else {
            notifIcon.style.display = 'none';
        }

        notifIcon.addEventListener('click', function () {
            if (notifContainer.style.display === 'none' || notifContainer.style.display === '') {
                notifContainer.style.display = 'block';
                loadNotifications();
            } else {
                notifContainer.style.display = 'none';
            }
        });

        closeNotifBtn.addEventListener('click', function () {
            notifContainer.style.display = 'none';
        });
    }

    // === Clear message div
    const message = document.getElementById('message');
    if (message) {
        message.innerHTML = '';
    }
});

// === Helper Functions ===
function normalizeDate(d) {
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function isDateInDisabledRanges(date) {
    const normalizedDate = normalizeDate(date);
    return disabledRanges.some(range => {
        const from = normalizeDate(new Date(range.from));
        const to = normalizeDate(new Date(range.to));
        return normalizedDate >= from && normalizedDate <= to;
    });
}

function highlightProposalDates(date, element) {
    if (isDateInDisabledRanges(date)) {
        element.style.borderBottom = "3px solid orange";
        element.style.fontWeight = "600";
        element.title = "Date already has proposal";
    }
}

// === Load Notifications from PHP-injected values ===
function loadNotifications() {
    const notifList = document.getElementById('notificationList');
    if (!notifList) return;

    notifList.innerHTML = ''; // Clear existing list

    // These items should be added server-side via inline PHP like:
    // echo "notifList.innerHTML += `<li class='list-group-item'>Proposal #$id - $eventType was disapproved.</li>`;";
    // Example fallback if PHP doesn't populate:
    if (notifList.children.length === 0) {
        notifList.innerHTML = '<li class="list-group-item text-center text-muted">No new notifications</li>';
    }
}

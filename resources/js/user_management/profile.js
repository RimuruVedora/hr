 // Activity Graph
        const ctx = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Contributions',
                    data: [12, 19, 15, 25, 22, 30, 28],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.05)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f8fafc' },
                        ticks: { color: '#94a3b8', font: { size: 12 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 12 } }
                    }
                }
            }
        });

        // Modal Controls
        function openPictureModal() {
            document.getElementById('pictureModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Sync preview with current profile pic
            document.getElementById('previewImage').src = document.getElementById('profilePicMain').src;
        }

        function closePictureModal() {
            document.getElementById('pictureModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openPasswordModal() {
            document.getElementById('passwordModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('passwordForm').reset();
            document.getElementById('matchError').classList.add('hidden');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Image Handling
        function previewFile() {
            const preview = document.getElementById('previewImage');
            const file = document.getElementById('imageUpload').files[0];
            const reader = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        function saveNewPicture() {
            const newSrc = document.getElementById('previewImage').src;
            document.getElementById('profilePicMain').src = newSrc;
            
            closePictureModal();
            
            document.getElementById('successText').innerText = "Your profile picture has been updated successfully.";
            document.getElementById('successModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function handlePasswordChange(event) {
            event.preventDefault();
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            const errorMsg = document.getElementById('matchError');

            if (newPass !== confirmPass) {
                errorMsg.classList.remove('hidden');
                return;
            }

            errorMsg.classList.add('hidden');
            closePasswordModal();
            
            setTimeout(() => {
                document.getElementById('successText').innerText = "Your password has been updated successfully. Please use your new credentials for next login.";
                document.getElementById('successModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }, 300);
        }
// Vehicle service booking: validation, dynamic display and cost estimate.

const CURRENT_YEAR = new Date().getFullYear();
const OLD_VEHICLE_YEAR = 2010;

const form = document.getElementById('serviceForm');
const vehicleTypeSelect = document.getElementById('vehicleType');
const yearInput = document.getElementById('year');

// Returns all checked service checkboxes.
function getCheckedServices() {
  return Array.from(document.querySelectorAll('input[name="services[]"]:checked'));
}

// Recalculates the estimate from the checked services' data-price values.
function calculateServiceCost() {
  const total = getCheckedServices()
    .reduce((sum, box) => sum + Number(box.dataset.price), 0);
  document.getElementById('estimatedTotal').textContent = 'KSh ' + total.toLocaleString('en-US');
  return total;
}

// Lists the selected services and shows how many there are.
function updateSelectedServices() {
  const checked = getCheckedServices();
  const list = document.getElementById('selectedList');
  list.innerHTML = '';
  if (checked.length === 0) {
    list.innerHTML = '<li>None</li>';
  } else {
    checked.forEach(box => {
      const item = document.createElement('li');
      item.textContent = box.value;
      list.appendChild(item);
    });
  }
  document.getElementById('serviceCount').textContent = checked.length;
}

// Shows a short description for the chosen vehicle type.
function showVehicleInfo() {
  const info = document.getElementById('vehicleInfo');
  const descriptions = {
    'Saloon Car': 'Saloon cars usually need oil changes, brake checks and wheel alignment.',
    'SUV': 'SUVs are heavier, so brake inspection and tyre balancing are recommended.',
    'Pickup': 'Pickups often carry loads; check brakes and wheel alignment regularly.',
    'Van': 'Vans carry heavy loads; a Full Service is advised for longer trips.'
  };
  const text = descriptions[vehicleTypeSelect.value];
  info.textContent = text || '';
  info.classList.toggle('hidden', !text);
}

// Warns when the vehicle was made before the reference year.
function checkVehicleAge() {
  const year = Number(yearInput.value);
  const warning = document.getElementById('oldVehicleWarning');
  warning.classList.toggle('hidden', !(year && year < OLD_VEHICLE_YEAR));
}

function setError(id, message) {
  const errorBox = document.getElementById(id);
  errorBox.textContent = message;
  const field = errorBox.parentElement.querySelector('input, select');
  if (field) field.classList.toggle('invalid', Boolean(message));
}

// Validates every field, shows messages, and returns true only if all pass.
function validateServiceForm() {
  const name = document.getElementById('customerName').value.trim();
  const phone = document.getElementById('phone').value.trim();
  const email = document.getElementById('email').value.trim();
  const reg = document.getElementById('regNumber').value.trim();
  const model = document.getElementById('vehicleModel').value.trim();
  const year = Number(yearInput.value);
  const serviceDate = document.getElementById('serviceDate').value;
  let valid = true;

  setError('errName', name ? '' : 'Customer name is required.');
  if (!name) valid = false;

  const phoneOk = /^\d{10}$/.test(phone);
  setError('errPhone', phoneOk ? '' : 'Phone number must be exactly 10 digits.');
  if (!phoneOk) valid = false;

  const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  setError('errEmail', emailOk ? '' : 'Enter a valid email address.');
  if (!emailOk) valid = false;

  setError('errReg', reg ? '' : 'Vehicle registration number is required.');
  if (!reg) valid = false;

  setError('errType', vehicleTypeSelect.value ? '' : 'Please select a vehicle type.');
  if (!vehicleTypeSelect.value) valid = false;

  setError('errModel', model ? '' : 'Vehicle model is required.');
  if (!model) valid = false;

  const yearOk = year >= 1980 && year <= CURRENT_YEAR;
  setError('errYear', yearOk ? '' : 'Year must be between 1980 and ' + CURRENT_YEAR + '.');
  if (!yearOk) valid = false;

  setError('errDate', serviceDate ? '' : 'Please choose a preferred service date.');
  if (!serviceDate) valid = false;

  const servicesOk = getCheckedServices().length > 0;
  setError('errServices', servicesOk ? '' : 'Select at least one service.');
  if (!servicesOk) valid = false;

  return valid;
}

// Event wiring.
vehicleTypeSelect.addEventListener('change', showVehicleInfo);
yearInput.addEventListener('input', checkVehicleAge);
document.querySelectorAll('input[name="services[]"]').forEach(box => {
  box.addEventListener('change', () => {
    calculateServiceCost();
    updateSelectedServices();
    setError('errServices', getCheckedServices().length ? '' : 'Select at least one service.');
  });
});
form.addEventListener('submit', event => {
  if (!validateServiceForm()) {
    event.preventDefault(); // Block submission while the form is invalid.
  }
});

// Initial state on page load.
calculateServiceCost();
updateSelectedServices();

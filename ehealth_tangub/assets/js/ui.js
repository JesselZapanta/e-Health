function showModal(message) {
  const modal = document.getElementById("ui-modal");
  const text = document.getElementById("ui-modal-text");
  text.innerText = message;
  modal.style.display = "flex";
}

function closeModal() {
  document.getElementById("ui-modal").style.display = "none";
}

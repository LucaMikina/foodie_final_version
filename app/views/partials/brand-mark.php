<?php
$velicinaLoga = $velicinaLoga ?? 'md';
$animirajLogo = $animirajLogo ?? false;
$animacijaKlasa = $animirajLogo ? ' brand-mark-breathe' : '';
?>
<span class="brand-mark-text brand-mark-text--<?= htmlspecialchars($velicinaLoga) ?><?= $animacijaKlasa ?>"><?php if ($animirajLogo): ?>Food<span class="bm-i-flame">ı<svg class="bm-flame" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C12 2 6 8 6 14C6 18 8.5 21 12 21C15.5 21 18 18 18 14C18 8 12 2 12 2Z" fill="#E6B71A"/><path d="M12 8C12 8 9.5 12 9.5 15C9.5 17 10.5 18.5 12 18.5C13.5 18.5 14.5 17 14.5 15C14.5 12 12 8 12 8Z" fill="#FDEEDD"/></svg></span>e<?php else: ?>Foodie<?php endif; ?></span><?php if ($animirajLogo): ?><span class="hero-cursor" style="height: <?= $velicinaLoga === 'lg' ? '2.6rem' : '1.6rem' ?>;" aria-hidden="true"></span><?php endif; ?>

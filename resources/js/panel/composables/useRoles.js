import { __ } from './useLang';

/*
 * Rol adi -> ceviri. Anahtarlar LITERAL yazildi: `__('user.role_' + role)`
 * PanelTranslationKeysTest'te `user.role_` diye yakalanip eksik anahtar sayilirdi.
 */
const ROLE_LABELS = {
  owner: () => __('user.role_owner'),
  admin: () => __('user.role_admin'),
  editor: () => __('user.role_editor'),
  author: () => __('user.role_author'),
  user: () => __('user.role_user'),
};

export function roleLabel(role) {
  return ROLE_LABELS[role]?.() ?? role;
}

export function roleOptions(roles) {
  return roles.map((role) => ({ value: role, label: roleLabel(role) }));
}

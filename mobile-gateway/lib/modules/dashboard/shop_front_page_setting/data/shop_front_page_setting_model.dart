class ShopFrontPageSettingModel {
  final int? id;
  final String? heroTitle;
  final String? heroSubtitle;
  final String? heroButtonText;
  final String? servicesBadgeText;
  final String? servicesTitle;
  final String? staffBadgeText;
  final String? staffTitle;
  final String? contactPhone;
  final String? contactEmail;
  final String? contactAddress;
  final String? googleMapsUrl;
  final String? footerText;
  final int? barberShopId;

  const ShopFrontPageSettingModel({
    this.id,
    this.heroTitle,
    this.heroSubtitle,
    this.heroButtonText,
    this.servicesBadgeText,
    this.servicesTitle,
    this.staffBadgeText,
    this.staffTitle,
    this.contactPhone,
    this.contactEmail,
    this.contactAddress,
    this.googleMapsUrl,
    this.footerText,
    this.barberShopId,
  });

  factory ShopFrontPageSettingModel.fromJson(Map<String, dynamic> json) => ShopFrontPageSettingModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      heroTitle: json['hero_title']?.toString(),
      heroSubtitle: json['hero_subtitle']?.toString(),
      heroButtonText: json['hero_button_text']?.toString(),
      servicesBadgeText: json['services_badge_text']?.toString(),
      servicesTitle: json['services_title']?.toString(),
      staffBadgeText: json['staff_badge_text']?.toString(),
      staffTitle: json['staff_title']?.toString(),
      contactPhone: json['contact_phone']?.toString(),
      contactEmail: json['contact_email']?.toString(),
      contactAddress: json['contact_address']?.toString(),
      googleMapsUrl: json['google_maps_url']?.toString(),
      footerText: json['footer_text']?.toString(),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'hero_title': heroTitle,
      'hero_subtitle': heroSubtitle,
      'hero_button_text': heroButtonText,
      'services_badge_text': servicesBadgeText,
      'services_title': servicesTitle,
      'staff_badge_text': staffBadgeText,
      'staff_title': staffTitle,
      'contact_phone': contactPhone,
      'contact_email': contactEmail,
      'contact_address': contactAddress,
      'google_maps_url': googleMapsUrl,
      'footer_text': footerText,
      'barber_shop_id': barberShopId,
  };
}
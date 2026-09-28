class BarberShopModel {
  final int? id;
  final String? ownerId;
  final String? name;
  final String? appName;
  final String? slug;
  final String? logo;
  final String? banner;
  final String? primaryColor;
  final String? secondaryColor;
  final String? trialEndsAt;
  final String? expiresAt;
  final bool? active;
  final bool? smsEnabled;
  final String? timezone;
  final int? maxNoShowBeforeBlock;

  const BarberShopModel({
    this.id,
    this.ownerId,
    this.name,
    this.appName,
    this.slug,
    this.logo,
    this.banner,
    this.primaryColor,
    this.secondaryColor,
    this.trialEndsAt,
    this.expiresAt,
    this.active,
    this.smsEnabled,
    this.timezone,
    this.maxNoShowBeforeBlock,
  });

  factory BarberShopModel.fromJson(Map<String, dynamic> json) => BarberShopModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      ownerId: json['owner_id']?.toString(),
      name: json['name']?.toString(),
      appName: json['app_name']?.toString(),
      slug: json['slug']?.toString(),
      logo: json['logo']?.toString(),
      banner: json['banner']?.toString(),
      primaryColor: json['primary_color']?.toString(),
      secondaryColor: json['secondary_color']?.toString(),
      trialEndsAt: json['trial_ends_at']?.toString(),
      expiresAt: json['expires_at']?.toString(),
      active: json['active'] == true || json['active'] == 1 || json['active'] == '1',
      smsEnabled: json['sms_enabled'] == true || json['sms_enabled'] == 1 || json['sms_enabled'] == '1',
      timezone: json['timezone']?.toString(),
      maxNoShowBeforeBlock: json['max_no_show_before_block'] == null ? null : int.tryParse(json['max_no_show_before_block'].toString()),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'owner_id': ownerId,
      'name': name,
      'app_name': appName,
      'slug': slug,
      'logo': logo,
      'banner': banner,
      'primary_color': primaryColor,
      'secondary_color': secondaryColor,
      'trial_ends_at': trialEndsAt,
      'expires_at': expiresAt,
      'active': active,
      'sms_enabled': smsEnabled,
      'timezone': timezone,
      'max_no_show_before_block': maxNoShowBeforeBlock,
  };
}
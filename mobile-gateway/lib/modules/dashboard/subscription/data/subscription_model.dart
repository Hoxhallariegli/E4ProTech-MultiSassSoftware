class SubscriptionModel {
  final int? id;
  final int? barberShopId;
  final int? planId;
  final String? startsAt;
  final String? endsAt;
  final String? status;
  final bool? autoRenew;

  const SubscriptionModel({
    this.id,
    this.barberShopId,
    this.planId,
    this.startsAt,
    this.endsAt,
    this.status,
    this.autoRenew,
  });

  factory SubscriptionModel.fromJson(Map<String, dynamic> json) => SubscriptionModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      planId: json['plan_id'] == null ? null : int.tryParse(json['plan_id'].toString()),
      startsAt: json['starts_at']?.toString(),
      endsAt: json['ends_at']?.toString(),
      status: json['status']?.toString(),
      autoRenew: json['auto_renew'] == true || json['auto_renew'] == 1 || json['auto_renew'] == '1',
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'plan_id': planId,
      'starts_at': startsAt,
      'ends_at': endsAt,
      'status': status,
      'auto_renew': autoRenew,
  };
}
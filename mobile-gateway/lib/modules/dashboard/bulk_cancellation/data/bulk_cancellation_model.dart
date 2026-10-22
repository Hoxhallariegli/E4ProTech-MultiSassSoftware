class BulkCancellationModel {
  final int? id;
  final int? barberShopId;
  final int? barberId;
  final String? startAt;
  final String? endAt;
  final String? reason;
  final int? affectedCount;

  const BulkCancellationModel({
    this.id,
    this.barberShopId,
    this.barberId,
    this.startAt,
    this.endAt,
    this.reason,
    this.affectedCount,
  });

  factory BulkCancellationModel.fromJson(Map<String, dynamic> json) => BulkCancellationModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      barberId: json['barber_id'] == null ? null : int.tryParse(json['barber_id'].toString()),
      startAt: json['start_at']?.toString(),
      endAt: json['end_at']?.toString(),
      reason: json['reason']?.toString(),
      affectedCount: json['affected_count'] == null ? null : int.tryParse(json['affected_count'].toString()),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'barber_id': barberId,
      'start_at': startAt,
      'end_at': endAt,
      'reason': reason,
      'affected_count': affectedCount,
  };
}
import 'package:flutter_test/flutter_test.dart';
import 'package:lam_shaml/core/arabic_normalizer.dart';

void main() {
  test('normalizes Arabic spelling variants', () {
    expect(ArabicNormalizer.normalize('أحمد عبد الرحمن'), 'احمد عبد رحمن');
    expect(ArabicNormalizer.normalize('هُدى مصطفى علي'), 'هدي مصطفي علي');
  });

  test('supports reordered name parts', () {
    expect(
      ArabicNormalizer.sameNameParts('أحمد سالم عبد الرحمن', 'عبدالرحمن سالم احمد'),
      isFalse,
    );
    expect(
      ArabicNormalizer.sameNameParts('هدى مصطفى علي', 'علي هدا مصطفي'),
      isTrue,
    );
  });
}

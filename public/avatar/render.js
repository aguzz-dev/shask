// Render compartido del avatar de Shhask (extraído de Index.blade.php).
// Requiere los catálogos de public/avatar/*.js cargados antes.
// Uso: renderShhaskAvatar('avatar', avatarJsonDelUsuario)
function renderShhaskAvatar(targetId, avatarUserData) {
    avatarUserData = avatarUserData || {};
            const parseURLParams = () => {
                return {
                    HairStyle: avatarUserData.HairStyle || 'Bald',
                    HairColor: avatarUserData.HairColor || 'Black',
                    FacialHairType: avatarUserData.FacialHairType || 'Nothing',
                    FacialHairColor: avatarUserData.FacialHairColor || 'Black',
                    EyeType: avatarUserData.EyeType || 'Default',
                    EyeBrowType: avatarUserData.EyebrowType || 'Default',
                    NoseType: avatarUserData.Nose || 'Default',
                    MouthType: avatarUserData.MouthType || 'Default',
                    SkinColor: avatarUserData.SkinColor || 'White',
                    OutfitType: avatarUserData.OutfitType || 'BlazerTShirt',
                    OutfitColor: avatarUserData.OutfitColor || 'Black',
                    Accessory: avatarUserData.Accessory || 'Nothing',
                };
            };



            const drawSVG = (properties) => {
                // Use SkinService for the skin SVG
                const skinSVG = SkinService.drawSVG({
                    skinColor: SkinColors[properties.SkinColor]?.svg || SkinColors.White.svg
                });

                // Use HairService for the hair SVG
                const hairStyle = HairStyles[properties.HairStyle] || HairStyles.Bald;
                const hairColor = HairColors[properties.HairColor]?.hexCode || HairColors.Black.hexCode;
                const hairSVG = HairService.drawSVG({
                    style: {
                        ...hairStyle,
                        svg: hairStyle?.svg.replaceAll('$TO_REPLACE_WITH_HAIRS_COLOR', hairColor),
                    },
                    color: {
                        hexCode: hairColor
                    },
                });

                // Use FacialHairService for the facial hair SVG
                const facialHairType = FacialHair[properties.FacialHairType];
                const facialHairColor = FacialHairColors[properties.FacialHairColor]?.hexCode || FacialHairColors.Black
                    .hexCode;
                const facialHairSVG = FacialHairService.drawSVG({
                    style: {
                        ...facialHairType,
                        svg: facialHairType?.svg.replaceAll('$TO_REPLACE_WITH_FACIAL_HAIRS_COLOR',
                            facialHairColor),
                    },
                    color: {
                        hexCode: facialHairColor
                    },
                });

                // Use EyesService for the eyes SVG
                const eyeStyle = Eyes[properties.EyeType]?.svg || '';
                const eyeSVG = EyesService.drawSVG({
                    eye: eyeStyle
                });

                // Use EyebrowService for the eye brow SVG
                const eyeBrowType = Eyebrows[properties.EyeBrowType]?.svg || '';
                const eyeBrowSVG = EyesBrowsService.drawSVG({
                    eyebrowType: eyeBrowType
                });

                // Use MouthService for the mouth SVG
                const mouthType = Mouths[properties.MouthType]?.svg || '';
                const mouthSVG = MouthsService.drawSVG({
                    mouthType: mouthType
                });

                // Use OutfitsService for the hair SVG
                const outfitStyle = Outfits[properties.OutfitType];
                const outfitColor = OutfitColors[properties.OutfitColor]?.hexCode || OutfitColors.Black.hexCode;
                const outfitSVG = OutfitsService.drawSVG({
                    style: {
                        ...outfitStyle,
                        svg: outfitStyle?.svg.replaceAll('$TO_REPLACE_WITH_OUTFIT_COLOR', outfitColor),
                    },
                    color: {
                        hexCode: outfitColor
                    },
                });

                // Use NoseService for the nose SVG
                const noseType = Noses[properties.NoseType]?.svg || '';
                const noseSVG = NosesService.drawSVG({
                    noseType: noseType
                });

                // Use AccessoriesService for the accessories SVG
                const accessorieType = Accessories[properties.Accessory]?.svg || '';
                const accessoriesSVG = AccessoriesService.drawSVG({
                    accessorieType: accessorieType
                });

                return `
                <svg width="264px" height="280px" viewBox="0 0 264 280" xmlns="http://www.w3.org/2000/svg">
                    <g>
                        ${skinSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${mouthSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${noseSVG}
                    </g>
                    <g transform="translate(9.5, 2)">
                        ${facialHairSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${eyeSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${eyeBrowSVG}
                    </g>

                    <g transform="translate(10, 0)">
                        ${hairSVG}
                    </g>

                    <g transform="translate(28, 100)">
                        ${outfitSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${accessoriesSVG}
                    </g>
                </svg>
            `;
            };

            const renderAvatar = () => {
                const properties = parseURLParams();

                // Validate properties
                if (!HairStyles[properties.HairStyle]) {
                    console.warn(`Invalid hairStyle: ${properties.HairStyle}`);
                    properties.HairStyle = 'Bald';
                }
                if (!HairColors[properties.HairColor]) {
                    console.warn(`Invalid hairColor: ${properties.HairColor}`);
                    properties.HairColor = 'Black';
                }
                if (!SkinColors[properties.SkinColor]) {
                    console.warn(`Invalid skinColor: ${properties.SkinColor}`);
                    properties.SkinColor = 'White';
                }
                if (!FacialHair[properties.FacialHairType]) {
                    console.warn(`Invalid facialHairType: ${properties.FacialHairType}`);
                    properties.FacialHairType = 'Nothing';
                }
                if (!FacialHairColors[properties.FacialHairColor]) {
                    console.warn(`Invalid facialHairColor: ${properties.FacialHairColor}`);
                    properties.FacialHairColor = 'Black';
                }
                if (!Eyes[properties.EyeType]) {
                    console.warn(`Invalid eyeType: ${properties.EyeType}`);
                    properties.EyeType = 'Default';
                }
                if (!Eyebrows[properties.EyeBrowType]) {
                    console.warn(`Invalid eyeBrowType: ${properties.EyeBrowType}`);
                    properties.EyeBrowType = 'Default';
                }
                if (!Mouths[properties.MouthType]) {
                    console.warn(`Invalid mouthType: ${properties.MouthType}`);
                    properties.MouthType = 'Default';
                }
                if (!Outfits[properties.OutfitType]) {
                    console.warn(`Invalid outfitType: ${properties.OutfitType}`);
                    properties.OutfitType = 'BlazerTShirt';
                }
                if (!OutfitColors[properties.OutfitColor]) {
                    console.warn(`Invalid outfitColor: ${properties.OutfitColor}`);
                    properties.OutfitColor = 'Black';
                }
                if (!Noses[properties.NoseType]) {
                    console.warn(`Invalid noseType: ${properties.NoseType}`);
                    properties.NoseType = 'Default';
                }
                if (!Accessories[properties.Accessory]) {
                    console.warn(`Invalid accessoriesType: ${properties.Accessory}`);
                    properties.Accessory = 'Nothing';
                }

                const avatarElement = document.getElementById(targetId);
                avatarElement.innerHTML = drawSVG(properties);
            };
    renderAvatar();
}
